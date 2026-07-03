<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'selesai') {
            return back()->with('error', 'Ulasan hanya dapat dikirim untuk pesanan yang sudah selesai.');
        }

        // Check if user already reviewed this product for this order
        $exists = Review::where('user_id', auth()->id())
            ->where('order_id', $request->order_id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah mengulas produk ini untuk pesanan ini.');
        }

        Review::create([
            'user_id' => auth()->id(),
            'order_id' => $request->order_id,
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Ulasan berhasil dikirim!');
    }
}
