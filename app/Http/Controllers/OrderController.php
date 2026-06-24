<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentConfirmation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('paymentConfirmation')
            ->latest()
            ->paginate(10)
            ->through(fn ($order) => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'status_color' => $order->status_color,
                'has_payment' => $order->paymentConfirmation !== null,
                'created_at' => $order->created_at->format('d M Y H:i'),
            ]);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.product', 'paymentConfirmation']);

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'total_amount' => $order->total_amount,
                'payment_method' => $order->payment_method,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'status_color' => $order->status_color,
                'shipping_name' => $order->shipping_name,
                'shipping_phone' => $order->shipping_phone,
                'shipping_address' => $order->shipping_address,
                'shipping_province' => $order->shipping_province,
                'shipping_city' => $order->shipping_city,
                'shipping_district' => $order->shipping_district,
                'shipping_village' => $order->shipping_village,
                'shipping_postal_code' => $order->shipping_postal_code,
                'notes' => $order->notes,
                'created_at' => $order->created_at->format('d M Y H:i'),
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ]),
                'payment' => $order->paymentConfirmation ? [
                    'id' => $order->paymentConfirmation->id,
                    'sender_name' => $order->paymentConfirmation->sender_name,
                    'sender_bank' => $order->paymentConfirmation->sender_bank,
                    'amount' => $order->paymentConfirmation->amount,
                    'transfer_date' => $order->paymentConfirmation->transfer_date->format('d M Y'),
                    'proof_image_url' => $order->paymentConfirmation->proof_image_url,
                    'status' => $order->paymentConfirmation->status,
                ] : null,
            ],
        ]);
    }

    public function uploadPayment(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Orders/UploadPayment', [
            'order' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'total_amount' => $order->total_amount,
            ],
        ]);
    }

    public function storePayment(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_bank' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'transfer_date' => 'required|date',
            'proof_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = $request->file('proof_image')->store('payment-proofs', 'public');

        PaymentConfirmation::create([
            'order_id' => $order->id,
            'sender_name' => $request->sender_name,
            'sender_bank' => $request->sender_bank,
            'amount' => $request->amount,
            'transfer_date' => $request->transfer_date,
            'proof_image' => $path,
        ]);

        $order->update(['status' => 'menunggu_verifikasi']);

        return redirect()->route('orders.show', $order)->with('success', 'Bukti pembayaran berhasil diupload!');
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'menunggu_pembayaran') {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan.');
        }

        $order->update(['status' => 'dibatalkan']);

        // Restore stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->qty);
        }

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function complete(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'dikirim') {
            return back()->with('error', 'Pesanan belum dikirim.');
        }

        $order->update(['status' => 'selesai']);

        return back()->with('success', 'Pesanan dikonfirmasi selesai!');
    }
}
