<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display a listing of orders (for store owner)
     */
    public function index(Request $request)
    {
        $query = Order::with('items');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);

        return response()->json($orders);
    }

    /**
     * Checkout - create order from cart
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email',
            'customer_address' => 'required|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $sessionId = $request->session()->getId();
        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'error' => 'Cart is empty'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = $cart->total();
            $shippingCost = $validated['shipping_cost'] ?? 0;
            $total = $subtotal + $shippingCost;

            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . time() . '-' . rand(1000, 9999),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_address' => $validated['customer_address'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->price * $item->quantity,
                ]);

                // Update product stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear cart
            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            // Load order items for response
            $order->load('items');

            // Send WhatsApp notification to store owner
            $store = $request->attributes->get('store');
            if ($store) {
                $this->whatsappService->sendCheckoutNotification($order, $store->whatsapp);
            }

            return response()->json([
                'order' => $order,
                'message' => 'Order created successfully. The store will contact you via WhatsApp shortly.',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load('items.product');
        return response()->json($order);
    }

    /**
     * Update order status (for store owner)
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $order->notes,
        ]);

        // Update timestamps based on status
        switch ($validated['status']) {
            case 'confirmed':
                $order->update(['confirmed_at' => now()]);
                break;
            case 'shipped':
                $order->update(['shipped_at' => now()]);
                break;
            case 'delivered':
                $order->update(['delivered_at' => now()]);
                break;
        }

        // Send WhatsApp notification to customer about status change
        if ($oldStatus !== $validated['status']) {
            $this->whatsappService->sendOrderStatusUpdate($order, $validated['status']);
        }

        return response()->json([
            'order' => $order,
            'message' => 'Order status updated successfully',
        ]);
    }
}

