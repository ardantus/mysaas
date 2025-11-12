<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get or create cart for current session
     */
    protected function getCart(Request $request): Cart
    {
        $sessionId = $request->session()->getId();
        
        $cart = Cart::firstOrCreate(
            ['session_id' => $sessionId],
            ['user_id' => $request->user()?->id]
        );

        return $cart;
    }

    /**
     * Show cart contents
     */
    public function show(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load('items.product');

        return response()->json([
            'cart' => $cart,
            'total' => $cart->total(),
        ]);
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock < $validated['quantity']) {
            return response()->json([
                'error' => 'Insufficient stock'
            ], 400);
        }

        $cart = $this->getCart($request);

        // Check if item already exists in cart
        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            $cartItem->quantity += $validated['quantity'];
            $cartItem->save();
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'price' => $product->price,
            ]);
        }

        $cart->load('items.product');

        return response()->json([
            'cart' => $cart,
            'total' => $cart->total(),
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, CartItem $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($item->product->stock < $validated['quantity']) {
            return response()->json([
                'error' => 'Insufficient stock'
            ], 400);
        }

        $item->update($validated);
        $cart = $item->cart;
        $cart->load('items.product');

        return response()->json([
            'cart' => $cart,
            'total' => $cart->total(),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(CartItem $item)
    {
        $cart = $item->cart;
        $item->delete();
        
        $cart->load('items.product');

        return response()->json([
            'cart' => $cart,
            'total' => $cart->total(),
        ]);
    }

    /**
     * Clear cart
     */
    public function clear(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->items()->delete();

        return response()->json([
            'message' => 'Cart cleared',
        ]);
    }
}

