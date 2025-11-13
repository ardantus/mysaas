<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;

    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url', 'https://api.whatsapp.com/send');
        $this->apiKey = config('services.whatsapp.api_key', '');
    }

    /**
     * Send checkout notification to store owner
     */
    public function sendCheckoutNotification(Order $order, string $storeWhatsapp): bool
    {
        $message = $this->formatCheckoutMessage($order);

        return $this->sendMessage($storeWhatsapp, $message);
    }

    /**
     * Send order status update to customer
     */
    public function sendOrderStatusUpdate(Order $order, string $status): bool
    {
        $message = $this->formatStatusUpdateMessage($order, $status);

        return $this->sendMessage($order->customer_phone, $message);
    }

    /**
     * Format checkout message
     */
    protected function formatCheckoutMessage(Order $order): string
    {
        $message = "🛍️ *Pesanan Baru!*\n\n";
        $message .= "📝 Order: #{$order->order_number}\n";
        $message .= "👤 Nama: {$order->customer_name}\n";
        $message .= "📱 HP: {$order->customer_phone}\n";
        $message .= "📍 Alamat: {$order->customer_address}\n\n";
        $message .= "*Detail Produk:*\n";

        foreach ($order->items as $item) {
            $message .= "- {$item->product_name} x{$item->quantity} - Rp ".number_format($item->subtotal, 0, ',', '.')."\n";
        }

        $message .= "\n💰 *Total: Rp ".number_format($order->total, 0, ',', '.')."*\n\n";
        $message .= 'Silakan hubungi pelanggan untuk konfirmasi pesanan.';

        return $message;
    }

    /**
     * Format status update message
     */
    protected function formatStatusUpdateMessage(Order $order, string $status): string
    {
        $statusMessages = [
            'confirmed' => '✅ Pesanan Anda telah dikonfirmasi',
            'processing' => '⏳ Pesanan Anda sedang diproses',
            'shipped' => '🚚 Pesanan Anda sudah dikirim',
            'delivered' => '✅ Pesanan Anda sudah sampai',
            'cancelled' => '❌ Pesanan Anda dibatalkan',
        ];

        $message = "📦 *Update Pesanan*\n\n";
        $message .= "Order: #{$order->order_number}\n";
        $message .= "Status: {$statusMessages[$status]}\n\n";

        if ($order->notes) {
            $message .= "Catatan: {$order->notes}\n\n";
        }

        $message .= 'Terima kasih telah berbelanja dengan kami! 🙏';

        return $message;
    }

    /**
     * Send WhatsApp message
     */
    protected function sendMessage(string $phone, string $message): bool
    {
        try {
            // Clean phone number
            $phone = preg_replace('/[^0-9]/', '', $phone);

            // Add country code if not present (default Indonesia)
            if (! str_starts_with($phone, '62')) {
                if (str_starts_with($phone, '0')) {
                    $phone = '62'.substr($phone, 1);
                } else {
                    $phone = '62'.$phone;
                }
            }

            // Using WhatsApp Web URL (can be replaced with API provider like Twilio, Vonage, etc.)
            $url = "https://wa.me/{$phone}?text=".urlencode($message);

            // Log the WhatsApp message for debugging
            Log::info('WhatsApp message prepared', [
                'phone' => $phone,
                'message' => $message,
                'url' => $url,
            ]);

            // In production, integrate with WhatsApp Business API
            // For now, we'll just return true and log
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get WhatsApp Web link for manual sending
     */
    public function getWhatsAppLink(string $phone, string $message): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (! str_starts_with($phone, '62')) {
            if (str_starts_with($phone, '0')) {
                $phone = '62'.substr($phone, 1);
            } else {
                $phone = '62'.$phone;
            }
        }

        return "https://wa.me/{$phone}?text=".urlencode($message);
    }
}
