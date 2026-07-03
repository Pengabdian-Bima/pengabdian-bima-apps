<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentConfirmation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::where('user_id', auth()->id())
            ->with('paymentConfirmation');

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->latest()
            ->paginate(10)
            ->withQueryString()
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
            'filters' => $request->only(['start_date', 'end_date']),
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
                'rejection_reason' => $order->rejection_reason,
                'created_at' => $order->created_at->format('d M Y H:i'),
                'items' => $order->items->map(function ($item) use ($order) {
                    $review = \App\Models\Review::where('order_id', $order->id)
                        ->where('product_id', $item->product_id)
                        ->first();
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'qty' => $item->qty,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                        'review' => $review ? [
                            'rating' => $review->rating,
                            'comment' => $review->comment,
                        ] : null,
                    ];
                }),
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



    public function storePayment(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($order->status, ['menunggu_pembayaran', 'menunggu_verifikasi', 'ditolak'])) {
            return back()->with('error', 'Pesanan ini tidak dapat diupload bukti pembayarannya.');
        }

        $request->validate([
            'sender_name' => 'nullable|string|max:255',
            'sender_bank' => 'nullable|string|max:100',
            'amount' => 'nullable|numeric|min:1',
            'transfer_date' => 'nullable|date',
            'proof_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // If the order was rejected, validate stock and decrement it again before proceeding
        if ($order->status === 'ditolak') {
            foreach ($order->items as $item) {
                if ($item->qty > $item->product->stock) {
                    return back()->with('error', "Stok produk {$item->product->name} tidak mencukupi untuk memproses ulang pesanan ini.");
                }
            }

            foreach ($order->items as $item) {
                $product = $item->product;
                $stockBefore = $product->stock;
                $product->decrement('stock', $item->qty);

                \App\Models\StockHistory::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $item->qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock,
                    'note' => "Pembayaran ulang Pesanan #{$order->order_code}",
                ]);
            }
        }

        $path = $request->file('proof_image')->store('payment-proofs', 'public');

        $paymentConfirmation = PaymentConfirmation::where('order_id', $order->id)->first();

        if ($paymentConfirmation) {
            if ($paymentConfirmation->proof_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($paymentConfirmation->proof_image);
            }

            $paymentConfirmation->update([
                'sender_name' => $request->sender_name ?? auth()->user()->name ?? $paymentConfirmation->sender_name,
                'sender_bank' => $request->sender_bank ?? ($order->payment_method === 'qris' ? 'QRIS' : 'Transfer Manual'),
                'amount' => $request->amount ?? $order->total_amount,
                'transfer_date' => $request->transfer_date ?? now()->toDateString(),
                'proof_image' => $path,
                'status' => 'pending',
            ]);
        } else {
            PaymentConfirmation::create([
                'order_id' => $order->id,
                'sender_name' => $request->sender_name ?? auth()->user()->name ?? 'Pembeli',
                'sender_bank' => $request->sender_bank ?? ($order->payment_method === 'qris' ? 'QRIS' : 'Transfer Manual'),
                'amount' => $request->amount ?? $order->total_amount,
                'transfer_date' => $request->transfer_date ?? now()->toDateString(),
                'proof_image' => $path,
                'status' => 'pending',
            ]);
        }

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
            $product = $item->product;
            $stockBefore = $product->stock;
            $product->increment('stock', $item->qty);

            \App\Models\StockHistory::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $item->qty,
                'stock_before' => $stockBefore,
                'stock_after' => $product->stock,
                'note' => "Pesanan Dibatalkan Pembeli #{$order->order_code}",
            ]);
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
