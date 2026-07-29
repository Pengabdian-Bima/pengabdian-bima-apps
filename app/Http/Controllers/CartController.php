<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->role !== 'user') {
            return redirect()->route('admin.dashboard')->with('error', 'Akun Admin/Non-User tidak memiliki akses ke fitur keranjang belanja.');
        }

        $cart = Cart::with(['items.product'])
            ->where('user_id', auth()->id())
            ->first();

        $items = $cart ? $cart->items->map(function ($item) {
            $effectivePrice = $item->product->final_price;
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_slug' => $item->product->slug,
                'thumbnail_url' => $item->product->thumbnail_url,
                'original_price' => $item->product->price,
                'price' => $effectivePrice,
                'is_discount_active' => $item->product->is_discount_active,
                'discount_percent' => $item->product->discount_percent,
                'qty' => $item->qty,
                'subtotal' => $effectivePrice * $item->qty,
                'stock' => $item->product->stock,
            ];
        }) : collect();

        $total = $items->sum('subtotal');

        return Inertia::render('Cart/Index', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function add(Request $request)
    {
        if (auth()->check() && auth()->user()->role !== 'user') {
            return back()->with('error', 'Akun Admin/Non-User tidak dapat menambahkan produk ke keranjang belanja.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->qty) {
            return back()->with('error', 'Stok produk tidak mencukupi.');
        }

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $newQty = $cartItem->qty + $request->qty;
            if ($newQty > $product->stock) {
                return back()->with('error', 'Jumlah melebihi stok tersedia.');
            }
            $cartItem->update([
                'qty' => $newQty,
                'price' => $product->final_price,
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'qty' => $request->qty,
                'price' => $product->final_price,
            ]);
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if (auth()->check() && auth()->user()->role !== 'user') {
            return back()->with('error', 'Aksi tidak diizinkan untuk akun Admin.');
        }

        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        if ($request->qty > $cartItem->product->stock) {
            return back()->with('error', 'Jumlah melebihi stok tersedia.');
        }

        $cartItem->update(['qty' => $request->qty]);

        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    public function destroy(CartItem $cartItem)
    {
        if (auth()->check() && auth()->user()->role !== 'user') {
            return back()->with('error', 'Aksi tidak diizinkan untuk akun Admin.');
        }

        $cartItem->delete();
        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }
}
