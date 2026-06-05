<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'paymentConfirmation']);
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15)->through(fn ($order) => [
            'id' => $order->id,
            'order_code' => $order->order_code,
            'user_name' => $order->user->name,
            'total_amount' => $order->total_amount,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'status_color' => $order->status_color,
            'has_payment' => $order->paymentConfirmation !== null,
            'created_at' => $order->created_at->format('d M Y H:i'),
        ]);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'currentStatus' => $request->status,
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'paymentConfirmation']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'status_color' => $order->status_color,
                'shipping_name' => $order->shipping_name,
                'shipping_phone' => $order->shipping_phone,
                'shipping_address' => $order->shipping_address,
                'notes' => $order->notes,
                'user' => ['name' => $order->user->name, 'email' => $order->user->email, 'phone' => $order->user->phone],
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ]),
                'payment' => $order->paymentConfirmation ? [
                    'sender_name' => $order->paymentConfirmation->sender_name,
                    'sender_bank' => $order->paymentConfirmation->sender_bank,
                    'amount' => $order->paymentConfirmation->amount,
                    'transfer_date' => $order->paymentConfirmation->transfer_date->format('d M Y'),
                    'proof_image_url' => $order->paymentConfirmation->proof_image_url,
                    'status' => $order->paymentConfirmation->status,
                ] : null,
                'created_at' => $order->created_at->format('d M Y H:i'),
            ],
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:diproses,dikirim,selesai,ditolak,dibatalkan']);
        $newStatus = $request->status;

        if ($newStatus === 'ditolak' && $order->paymentConfirmation) {
            $order->paymentConfirmation->update(['status' => 'rejected']);
            foreach ($order->items as $item) { $item->product->increment('stock', $item->qty); }
        }
        if ($newStatus === 'diproses' && $order->paymentConfirmation) {
            $order->paymentConfirmation->update(['status' => 'verified']);
        }
        if ($newStatus === 'dibatalkan') {
            foreach ($order->items as $item) { $item->product->increment('stock', $item->qty); }
        }

        $order->update(['status' => $newStatus]);
        return back()->with('success', 'Status pesanan berhasil diperbarui!');
    }
}
