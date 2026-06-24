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
            'weight' => $item->product->weight > 0 ? $item->product->weight : 200,
            'subtotal' => $item->subtotal,
        ]);

        $total = $items->sum('subtotal');

        return Inertia::render('Checkout/Index', [
            'items' => $items,
            'total' => $total,
            'user' => auth()->user(),
            'addresses' => auth()->user()->addresses()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'nullable|exists:user_addresses,id',
            'payment_method' => 'required|in:transfer,qris',
            'shipping_name' => 'required_without:address_id|nullable|string|max:255',
            'shipping_phone' => 'required_without:address_id|nullable|string|max:20',
            'shipping_address' => 'required_without:address_id|nullable|string',
            'shipping_province' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_city_id' => 'required_without:address_id|nullable',
            'shipping_district' => 'nullable|string',
            'shipping_village' => 'nullable|string',
            'shipping_postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'courier' => 'required|string|in:jne,pos,tiki',
            'courier_service' => 'required|string',
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

        // Retrieve shipping destination and calculate actual cost
        $destinationCityId = null;
        $shippingData = [];
        if ($request->address_id) {
            $address = \App\Models\UserAddress::where('user_id', auth()->id())
                ->findOrFail($request->address_id);
            $destinationCityId = $address->city_id;
            $shippingData = [
                'shipping_name' => $address->recipient_name,
                'shipping_phone' => $address->phone,
                'shipping_address' => $address->address,
                'shipping_province' => $address->province,
                'shipping_city' => $address->city,
                'shipping_city_id' => $address->city_id,
                'shipping_district' => $address->district,
                'shipping_village' => $address->village,
                'shipping_postal_code' => $address->postal_code,
            ];
        } else {
            $destinationCityId = $request->shipping_city_id;
            $shippingData = [
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'shipping_province' => $request->shipping_province,
                'shipping_city' => $request->shipping_city,
                'shipping_city_id' => $request->shipping_city_id,
                'shipping_district' => $request->shipping_district,
                'shipping_village' => $request->shipping_village,
                'shipping_postal_code' => $request->shipping_postal_code,
            ];
        }

        if (!$destinationCityId) {
            return back()->with('error', 'Kota tujuan pengiriman tidak valid.');
        }

        // Calculate total weight of the products (default to 200g if weight is 0 or null)
        $totalWeight = 0;
        foreach ($cart->items as $item) {
            $weight = $item->product->weight > 0 ? $item->product->weight : 200;
            $totalWeight += $weight * $item->qty;
        }

        // Fetch shipping cost from RajaOngkir
        $rajaOngkirService = app(\App\Services\RajaOngkirService::class);
        $shippingOptions = $rajaOngkirService->calculateCost($destinationCityId, $totalWeight, $request->courier);

        $calculatedCost = null;
        if (!empty($shippingOptions)) {
            foreach ($shippingOptions[0]['costs'] ?? [] as $costOption) {
                if ($costOption['service'] === $request->courier_service) {
                    $calculatedCost = $costOption['cost'][0]['value'] ?? null;
                    break;
                }
            }
        }

        if ($calculatedCost === null) {
            return back()->with('error', 'Layanan kurir atau ongkos kirim tidak valid.');
        }

        DB::transaction(function () use ($request, $cart, $shippingData, $calculatedCost) {
            $subtotal = $cart->items->sum(fn ($item) => $item->qty * $item->price);
            $total = $subtotal + $calculatedCost;

            $order = Order::create(array_merge([
                'user_id' => auth()->id(),
                'order_code' => Order::generateOrderCode(),
                'total_amount' => $total,
                'shipping_cost' => $calculatedCost,
                'courier' => $request->courier,
                'courier_service' => $request->courier_service,
                'payment_method' => $request->payment_method,
                'status' => 'menunggu_pembayaran',
                'notes' => $request->notes,
            ], $shippingData));

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
