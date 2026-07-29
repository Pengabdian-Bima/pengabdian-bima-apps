<?php

namespace App\Services;

use App\Models\PreOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected ?string $token;
    protected ?string $defaultTarget;

    public function __construct()
    {
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
}
