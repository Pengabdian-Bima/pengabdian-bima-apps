<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Cart::with(['items.product'])
            ->where('user_id', auth()->id())
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
        }

        $items = $cart->items->map(fn ($item) => [
            'id' => $item->id,
            'product_name' => $item->product->name,
            'price' => $item->price,
            'qty' => $item->qty,
            'subtotal' => $item->subtotal,
        ]);

        $total = $items->sum('subtotal');

        return Inertia::render('Checkout/Index', [
            'items' => $items,
            'total' => $total,
            'user' => auth()->user(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_province' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_district' => 'nullable|string',
            'shipping_village' => 'nullable|string',
            'shipping_postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $cart = Cart::with(['items.product'])
            ->where('user_id', auth()->id())
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
        }

        // Validate stock
        foreach ($cart->items as $item) {
            if ($item->qty > $item->product->stock) {
                return back()->with('error', "Stok {$item->product->name} tidak mencukupi.");
            }
        }

        DB::transaction(function () use ($request, $cart) {
            $total = $cart->items->sum(fn ($item) => $item->qty * $item->price);

            $order = Order::create([
                'user_id' => auth()->id(),
                'order_code' => Order::generateOrderCode(),
                'total_amount' => $total,
                'status' => 'menunggu_pembayaran',
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'shipping_province' => $request->shipping_province,
                'shipping_city' => $request->shipping_city,
                'shipping_district' => $request->shipping_district,
                'shipping_village' => $request->shipping_village,
                'shipping_postal_code' => $request->shipping_postal_code,
                'notes' => $request->notes,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'subtotal' => $item->qty * $item->price,
                ]);

                // Reduce stock
                $product = $item->product;
                $stockBefore = $product->stock;
                $product->decrement('stock', $item->qty);

                StockHistory::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $item->qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock,
                    'note' => "Pesanan #{$order->order_code}",
                ]);
            }

            // Clear cart
            $cart->items()->delete();
        });

        return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');
    }
}
