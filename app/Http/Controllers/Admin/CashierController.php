<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashierController extends Controller
{
    public function index()
    {
        $products = Product::where('status', true)
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'stock' => $p->stock,
                'thumbnail_url' => $p->thumbnail_url,
            ]);

        return Inertia::render('Admin/Cashier', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'cash_received' => 'required|numeric|min:0',
        ]);

        $productIds = collect($request->items)->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Calculate total
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) {
                return back()->with('error', 'Produk tidak ditemukan.');
            }
            if ($product->stock < $item['qty']) {
                return back()->with('error', "Stok produk {$product->name} tidak mencukupi (Tersedia: {$product->stock}).");
            }
            $totalAmount += $product->price * $item['qty'];
        }

        if ($request->cash_received < $totalAmount) {
            return back()->with('error', 'Jumlah uang pembayaran kurang.');
        }

        $order = DB::transaction(function () use ($request, $products, $totalAmount) {
            $orderCode = Order::generateOrderCode();
            
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_code' => $orderCode,
                'total_amount' => $totalAmount,
                'payment_method' => 'tunai',
                'status' => 'selesai',
                'shipping_name' => $request->customer_name,
                'shipping_phone' => $request->customer_phone ?? '-',
                'shipping_address' => 'Belanja Langsung di Kasir (Offline)',
                'shipping_cost' => 0,
                'courier' => 'none',
                'courier_service' => 'none',
            ]);

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $product->price,
                    'subtotal' => $product->price * $item['qty'],
                ]);

                $stockBefore = $product->stock;
                $product->decrement('stock', $item['qty']);

                StockHistory::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $item['qty'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock,
                    'note' => "Penjualan Kasir #{$orderCode}",
                ]);
            }

            return $order;
        });

        // Prepare receipt data
        $receipt = [
            'order_code' => $order->order_code,
            'customer_name' => $order->shipping_name,
            'customer_phone' => $order->shipping_phone,
            'total_amount' => $order->total_amount,
            'cash_received' => $request->cash_received,
            'change' => $request->cash_received - $totalAmount,
            'date' => $order->created_at->format('d-m-Y H:i:s'),
            'items' => collect($request->items)->map(function ($item) use ($products) {
                $product = $products->get($item['product_id']);
                return [
                    'name' => $product->name,
                    'qty' => $item['qty'],
                    'price' => $product->price,
                    'subtotal' => $product->price * $item['qty'],
                ];
            })->toArray(),
        ];

        return redirect()->route('admin.cashier.index')
            ->with('success', 'Transaksi kasir berhasil diproses!')
            ->with('receipt', $receipt);
    }
}
