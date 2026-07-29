<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PreOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected bool $enabled;
    protected ?string $token;
    protected ?string $defaultTarget;

    public function __construct()
    {
        $this->enabled = (bool) config('services.fonnte.enabled', true);
        $this->token = config('services.fonnte.token');
        $this->defaultTarget = config('services.fonnte.target_phone');
    }

    /**
     * Send WhatsApp message via Fonnte API.
     *
     * @param string|null $target Target phone number(s)
     * @param string $message Text message
     * @return bool
     */
    public function sendMessage(?string $target, string $message): bool
    {
        if (!$this->enabled) {
            Log::info('Fonnte WhatsApp notification skipped: FONNTE_ENABLED is set to false.');
            return false;
        }

        $targetNumber = $target ?: $this->defaultTarget;

        if (empty($this->token)) {
            Log::warning('Fonnte WhatsApp notification skipped: FONNTE_TOKEN is not configured in .env');
            return false;
        }

        if (empty($targetNumber)) {
            Log::warning('Fonnte WhatsApp notification skipped: Target phone number is empty.');
            return false;
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $this->token,
                ])
                ->timeout(10)
                ->post('https://api.fonnte.com/send', [
                    'target'      => $targetNumber,
                    'message'     => $message,
                    'countryCode' => '62',
                ]);

            if ($response->successful()) {
                Log::info("Fonnte WA sent to {$targetNumber}: " . $response->body());
                return true;
            } else {
                Log::error("Fonnte WA failed to {$targetNumber}: Status {$response->status()} - " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Fonnte WA exception sending to {$targetNumber}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send new Pre-Order notification to Admin / Target number registered in system.
     *
     * @param PreOrder $preOrder
     * @return void
     */
    public function sendNewPreOrderNotification(PreOrder $preOrder): void
    {
        $preOrder->loadMissing(['items', 'user']);

        $itemListStr = "";
        foreach ($preOrder->items as $index => $item) {
            $num = $index + 1;
            $subtotal = number_format($item->subtotal, 0, ',', '.');
            $price = number_format($item->price, 0, ',', '.');
            $itemListStr .= "{$num}. *{$item->product_name}*\n   Qty: {$item->qty} x Rp {$price} = Rp {$subtotal}\n";
        }

        $totalFormatted = number_format($preOrder->total_amount, 0, ',', '.');
        $dateFormatted = $preOrder->created_at ? $preOrder->created_at->format('d M Y H:i') : now()->format('d M Y H:i');

        $fullAddress = array_filter([
            $preOrder->shipping_address,
            $preOrder->shipping_village,
            $preOrder->shipping_district,
            $preOrder->shipping_city,
            $preOrder->shipping_province,
            $preOrder->shipping_postal_code,
        ]);
        $addressStr = implode(', ', $fullAddress);

        $notes = $preOrder->notes ? $preOrder->notes : '-';

        $adminMessage = "📦 *NOTIFIKASI PRE-ORDER BARU* 📦\n\n"
            . "Ada pesanan Pre-Order baru yang masuk di sistem!\n\n"
            . "*Detail Pre-Order:*\n"
            . "• Kode PO: *#{$preOrder->po_code}*\n"
            . "• Tanggal: {$dateFormatted}\n"
            . "• Nama Pemesan: *{$preOrder->shipping_name}*\n"
            . "• No. HP Pemesan: {$preOrder->shipping_phone}\n\n"
            . "*Rincian Produk:*\n"
            . "{$itemListStr}\n"
            . "*Total Transaksi:* Rp {$totalFormatted}\n\n"
            . "*Alamat Pengiriman:*\n"
            . "{$addressStr}\n\n"
            . "*Catatan:* {$notes}\n\n"
            . "_Pesan ini dikirim otomatis oleh sistem Fonnte._";

        // Send to admin target number registered in Fonnte config
        if (!empty($this->defaultTarget)) {
            $this->sendMessage($this->defaultTarget, $adminMessage);
        } else {
            Log::warning("Fonnte: FONNTE_TARGET_PHONE is not set in .env");
        }
    }

    /**
     * Send Pre-Order status update notification to customer.
     *
     * @param PreOrder $preOrder
     * @param string $statusTitle
     * @param string|null $extraInfo
     * @return void
     */
    public function sendCustomerStatusNotification(PreOrder $preOrder, string $statusTitle, ?string $extraInfo = null): void
    {
        if (empty($preOrder->shipping_phone)) {
            return;
        }

        $totalFormatted = number_format($preOrder->total_amount, 0, ',', '.');

        $message = "📢 *UPDATE STATUS PRE-ORDER #{$preOrder->po_code}*\n\n"
            . "Halo *{$preOrder->shipping_name}*,\n"
            . "Status Pre-Order Anda saat ini: *{$statusTitle}*\n\n"
            . "*Total Transaksi:* Rp {$totalFormatted}\n";

        if ($extraInfo) {
            $message .= "\n*Keterangan:* {$extraInfo}\n";
        }

        $message .= "\nTerima kasih telah berbelanja bersama kami!\n"
            . "_Pesan ini dikirim otomatis oleh sistem._";

        $this->sendMessage($preOrder->shipping_phone, $message);
    }

    /**
     * Send new Order notification to Admin / Target number registered in system.
     *
     * @param Order $order
     * @return void
     */
    public function sendNewOrderNotification(Order $order): void
    {
        $order->loadMissing(['items.product', 'user']);

        $itemListStr = "";
        foreach ($order->items as $index => $item) {
            $num = $index + 1;
            $subtotal = number_format($item->subtotal, 0, ',', '.');
            $price = number_format($item->price, 0, ',', '.');
            $itemListStr .= "{$num}. *{$item->product_name}*\n   Qty: {$item->qty} x Rp {$price} = Rp {$subtotal}\n";
        }

        $totalFormatted = number_format($order->total_amount, 0, ',', '.');
        $shippingCostFormatted = number_format($order->shipping_cost ?? 0, 0, ',', '.');
        $dateFormatted = $order->created_at ? $order->created_at->format('d M Y H:i') : now()->format('d M Y H:i');

        $fullAddress = array_filter([
            $order->shipping_address,
            $order->shipping_village,
            $order->shipping_district,
            $order->shipping_city,
            $order->shipping_province,
            $order->shipping_postal_code,
        ]);
        $addressStr = implode(', ', $fullAddress);

        $notes = $order->notes ? $order->notes : '-';
        $courierStr = strtoupper($order->courier ?? '-') . ' (' . ($order->courier_service ?? '-') . ')';
        $paymentMethodStr = strtoupper($order->payment_method ?? '-');

        $adminMessage = "🛍️ *NOTIFIKASI PESANAN BARU* 🛍️\n\n"
            . "Ada pesanan baru yang masuk di toko online!\n\n"
            . "*Detail Pesanan:*\n"
            . "• Kode Pesanan: *#{$order->order_code}*\n"
            . "• Tanggal: {$dateFormatted}\n"
            . "• Nama Pemesan: *{$order->shipping_name}*\n"
            . "• No. HP Pemesan: {$order->shipping_phone}\n\n"
            . "*Rincian Produk:*\n"
            . "{$itemListStr}\n"
            . "*Ongkos Kirim ({$courierStr}):* Rp {$shippingCostFormatted}\n"
            . "*Total Transaksi:* Rp {$totalFormatted}\n"
            . "*Metode Pembayaran:* {$paymentMethodStr}\n\n"
            . "*Alamat Pengiriman:*\n"
            . "{$addressStr}\n\n"
            . "*Catatan:* {$notes}\n\n"
            . "_Pesan ini dikirim otomatis oleh sistem Fonnte._";

        if (!empty($this->defaultTarget)) {
            $this->sendMessage($this->defaultTarget, $adminMessage);
        } else {
            Log::warning("Fonnte: FONNTE_TARGET_PHONE is not set in .env");
        }
    }

    /**
     * Send notification to Admin when Customer uploads Payment Proof for standard order.
     *
     * @param Order $order
     * @return void
     */
    public function sendOrderPaymentProofNotification(Order $order): void
    {
        $order->loadMissing(['paymentConfirmation']);

        $payment = $order->paymentConfirmation;
        $totalFormatted = number_format($order->total_amount, 0, ',', '.');
        $amountPaidFormatted = $payment ? number_format($payment->amount, 0, ',', '.') : $totalFormatted;
        $senderName = $payment ? $payment->sender_name : $order->shipping_name;
        $senderBank = $payment ? $payment->sender_bank : '-';

        $adminMessage = "💳 *BUKTI PEMBAYARAN BARU DIUNGBANG* 💳\n\n"
            . "Pelanggan telah mengunggah bukti pembayaran untuk Pesanan *#{$order->order_code}*!\n\n"
            . "• Nama Pemesan: *{$order->shipping_name}*\n"
            . "• Nama Pengirim: *{$senderName}*\n"
            . "• Bank / E-Wallet: *{$senderBank}*\n"
            . "• Nominal: Rp {$amountPaidFormatted} (Total: Rp {$totalFormatted})\n\n"
            . "Silakan periksa dan konfirmasi pembayaran di Dashboard Admin.\n"
            . "_Pesan ini dikirim otomatis oleh sistem._";

        if (!empty($this->defaultTarget)) {
            $this->sendMessage($this->defaultTarget, $adminMessage);
        }
    }

    /**
     * Send Order status update notification to customer.
     *
     * @param Order $order
     * @param string $statusTitle
     * @param string|null $extraInfo
     * @return void
     */
    public function sendOrderStatusNotification(Order $order, string $statusTitle, ?string $extraInfo = null): void
    {
        if (empty($order->shipping_phone)) {
            return;
        }

        $totalFormatted = number_format($order->total_amount, 0, ',', '.');

        $message = "📢 *UPDATE STATUS PESANAN #{$order->order_code}*\n\n"
            . "Halo *{$order->shipping_name}*,\n"
            . "Status Pesanan Anda saat ini: *{$statusTitle}*\n\n"
            . "*Total Transaksi:* Rp {$totalFormatted}\n";

        if ($extraInfo) {
            $message .= "\n*Keterangan:* {$extraInfo}\n";
        }

        $message .= "\nTerima kasih telah berbelanja di UD Flamboyan!\n"
            . "_Pesan ini dikirim otomatis oleh sistem._";

        $this->sendMessage($order->shipping_phone, $message);
    }
}
