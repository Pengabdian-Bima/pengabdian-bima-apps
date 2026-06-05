<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->latest()
            ->paginate(10)
            ->through(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category?->name,
                'stock' => $p->stock,
                'min_stock' => $p->min_stock,
                'is_low' => $p->stock <= $p->min_stock,
            ]);

        return Inertia::render('Admin/Stock/Index', [
            'products' => $products,
        ]);
    }

    public function history(Product $product)
    {
        $histories = StockHistory::where('product_id', $product->id)
            ->latest()
            ->paginate(20)
            ->through(fn ($h) => [
                'id' => $h->id,
                'type' => $h->type,
                'quantity' => $h->quantity,
                'stock_before' => $h->stock_before,
                'stock_after' => $h->stock_after,
                'note' => $h->note,
                'created_at' => $h->created_at->format('d M Y H:i'),
            ]);

        return Inertia::render('Admin/Stock/History', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
            ],
            'histories' => $histories,
        ]);
    }

    public function adjust(Request $request, Product $product)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $stockBefore = $product->stock;

        if ($request->type === 'in') {
            $product->increment('stock', $request->quantity);
        } else {
            if ($product->stock < $request->quantity) {
                return back()->with('error', 'Stok tidak mencukupi.');
            }
            $product->decrement('stock', $request->quantity);
        }

        StockHistory::create([
            'product_id' => $product->id,
            'type' => $request->type,
            'quantity' => $request->quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $product->stock,
            'note' => $request->note ?? ($request->type === 'in' ? 'Penambahan stok' : 'Pengurangan stok'),
        ]);

        return back()->with('success', 'Stok berhasil diperbarui!');
    }
}
