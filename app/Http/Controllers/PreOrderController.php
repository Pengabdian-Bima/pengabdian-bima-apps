<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PreOrder;
use App\Models\PreOrderItem;
use App\Models\Product;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PreOrderController extends Controller
{
    protected FonnteService $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    public function index()
    {
        $preOrders = PreOrder::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn ($po) => [
                'id'           => $po->id,
                'po_code'      => $po->po_code,
                'status'       => $po->status,
                'status_label' => $po->status_label,
                'status_color' => $po->status_color,
                'total_amount' => $po->total_amount,
                'created_at'   => $po->created_at->format('d M Y H:i'),
            ]);

        return Inertia::render('PreOrders/Index', [
            'preOrders' => $preOrders,
        ]);
    }

    public function create()
    {
        $products = Product::where('status', true)->latest()->get()->map(fn ($p) => [
            'id'            => $p->id,
            'name'          => $p->name,
            'price'         => $p->price,
            'stock'         => $p->stock,
            'weight'        => $p->weight > 0 ? $p->weight : 200,
            'thumbnail_url' => $p->thumbnail_url,
        ]);

        $addresses = auth()->user()->addresses()->latest()->get();

        return Inertia::render('PreOrders/Create', [
            'products'        => $products,
            'addresses'       => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.qty'          => 'required|integer|min:1',
            'notes'                => 'nullable|string|max:1000',
            'shipping_name'        => 'required|string|max:255',
            'shipping_phone'       => 'required|string|max:20',
            'shipping_address'     => 'required|string',
            'shipping_province'    => 'nullable|string|max:255',
            'shipping_city'        => 'nullable|string|max:255',
            'city_id'              => 'nullable|string|max:50',
            'shipping_district'    => 'nullable|string|max:255',
            'shipping_village'     => 'nullable|string|max:255',
            'shipping_postal_code' => 'nullable|string|max:10',
        ]);

        $productIds = collect($request->items)->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $totalAmount = 0;
        foreach ($request->items as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) {
                return back()->withErrors(['items' => 'Produk tidak ditemukan.']);
            }
            $totalAmount += $product->price * $item['qty'];
        }

        $po = PreOrder::create([
            'user_id'              => auth()->id(),
            'po_code'              => PreOrder::generatePoCode(),
            'status'               => 'pending',
            'notes'                => $request->notes,
            'total_amount'         => $totalAmount,
            'shipping_name'        => $request->shipping_name,
            'shipping_phone'       => $request->shipping_phone,
            'shipping_address'     => $request->shipping_address,
            'shipping_province'    => $request->shipping_province,
            'shipping_city'        => $request->shipping_city,
            'city_id'              => $request->city_id,
            'shipping_district'    => $request->shipping_district,
            'shipping_village'     => $request->shipping_village,
            'shipping_postal_code' => $request->shipping_postal_code,
        ]);

        foreach ($request->items as $item) {
            $product = $products->get($item['product_id']);
            PreOrderItem::create([
                'pre_order_id' => $po->id,
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'qty'          => $item['qty'],
                'price'        => $product->price,
                'subtotal'     => $product->price * $item['qty'],
            ]);
        }

        // Send WhatsApp notification via Fonnte
        $this->fonnteService->sendNewPreOrderNotification($po);

        return redirect()->route('pre-orders.show', $po->id)
            ->with('success', 'Pre-Order berhasil dibuat! Kami akan segera menghubungi Anda.');
    }

    public function show(PreOrder $preOrder)
    {
        if ($preOrder->user_id !== auth()->id()) {
            abort(403);
        }

        $preOrder->load(['items.product']);

        return Inertia::render('PreOrders/Show', [
            'preOrder' => [
                'id'                   => $preOrder->id,
                'po_code'              => $preOrder->po_code,
                'status'               => $preOrder->status,
                'status_label'         => $preOrder->status_label,
                'status_color'         => $preOrder->status_color,
                'notes'                => $preOrder->notes,
                'rejection_reason'     => $preOrder->rejection_reason,
                'estimated_days'       => $preOrder->estimated_days,
                'total_amount'         => $preOrder->total_amount,
                'shipping_cost'        => $preOrder->shipping_cost ?? 0,
                'shipping_name'        => $preOrder->shipping_name,
                'shipping_phone'       => $preOrder->shipping_phone,
                'shipping_address'     => $preOrder->shipping_address,
                'shipping_province'    => $preOrder->shipping_province,
                'shipping_city'        => $preOrder->shipping_city,
                'shipping_district'    => $preOrder->shipping_district,
                'shipping_village'     => $preOrder->shipping_village,
                'shipping_postal_code' => $preOrder->shipping_postal_code,
                'city_id'              => $preOrder->city_id,
                'courier'              => $preOrder->courier,
                'courier_service'      => $preOrder->courier_service,
                'payment_method'       => $preOrder->payment_method,
                'created_at'           => $preOrder->created_at->format('d M Y H:i'),
                'payment_proof_url'    => $preOrder->payment_proof
                    ? asset('storage/' . $preOrder->payment_proof)
                    : null,
                'payment_sender_name'  => $preOrder->payment_sender_name,
                'payment_sender_bank'  => $preOrder->payment_sender_bank,
                'payment_amount'       => $preOrder->payment_amount,
                'payment_date'         => $preOrder->payment_date
                    ? $preOrder->payment_date->format('d M Y')
                    : null,
                'items'                => $preOrder->items->map(fn ($item) => [
                    'id'           => $item->id,
                    'product_name' => $item->product_name,
                    'qty'          => $item->qty,
                    'price'        => $item->price,
                    'weight'       => ($item->product && $item->product->weight > 0) ? $item->product->weight : 200,
                    'subtotal'     => $item->subtotal,
                ]),
            ],
        ]);
    }

    public function selectShipping(PreOrder $preOrder)
    {
        if ($preOrder->user_id !== auth()->id()) abort(403);
        if ($preOrder->status !== 'accepted') {
            return redirect()->route('pre-orders.show', $preOrder->id);
        }

        $preOrder->load(['items.product']);

        $totalWeight = $preOrder->items->reduce(function ($sum, $item) {
            $w = ($item->product && $item->product->weight > 0) ? $item->product->weight : 200;
            return $sum + ($w * $item->qty);
        }, 0);

        return Inertia::render('PreOrders/SelectShipping', [
            'preOrder' => [
                'id'                   => $preOrder->id,
                'po_code'              => $preOrder->po_code,
                'total_amount'         => $preOrder->total_amount,
                'estimated_days'       => $preOrder->estimated_days,
                'city_id'              => $preOrder->city_id,
                'shipping_name'        => $preOrder->shipping_name,
                'shipping_phone'       => $preOrder->shipping_phone,
                'shipping_address'     => $preOrder->shipping_address,
                'shipping_city'        => $preOrder->shipping_city,
                'items'                => $preOrder->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'qty'          => $item->qty,
                    'price'        => $item->price,
                    'weight'       => ($item->product && $item->product->weight > 0) ? $item->product->weight : 200,
                    'subtotal'     => $item->subtotal,
                ]),
            ],
            'totalWeight' => $totalWeight,
        ]);
    }

    public function storeShipping(Request $request, PreOrder $preOrder)
    {
        if ($preOrder->user_id !== auth()->id()) abort(403);
        if ($preOrder->status !== 'accepted') {
            return back()->withErrors(['status' => 'PO ini tidak dalam status yang tepat.']);
        }

        $request->validate([
            'courier'          => 'required|string|in:jne,jnt,sicepat,ninja,pos',
            'courier_service'  => 'required|string',
            'shipping_cost'    => 'required|integer|min:0',
            'payment_method'   => 'required|in:transfer,qris',
        ]);

        $preOrder->load(['items.product']);
        $totalWeight = $preOrder->items->reduce(function ($sum, $item) {
            $w = ($item->product && $item->product->weight > 0) ? $item->product->weight : 200;
            return $sum + ($w * $item->qty);
        }, 0);

        // Fetch shipping cost from RajaOngkir
        $rajaOngkirService = app(\App\Services\RajaOngkirService::class);
        $shippingOptions = $rajaOngkirService->calculateCost($preOrder->city_id, $totalWeight, $request->courier);

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

        $preOrder->update([
            'courier'         => strtoupper($request->courier),
            'courier_service' => $request->courier_service,
            'shipping_cost'   => $calculatedCost,
            'payment_method'  => $request->payment_method,
            'total_amount'    => $preOrder->total_amount + $calculatedCost,
            'status'          => 'processing',
        ]);

        // Reduce stock
        foreach ($preOrder->items as $item) {
            $product = $item->product;
            if ($product) {
                $stockBefore = $product->stock;
                $product->decrement('stock', $item->qty);
                \App\Models\StockHistory::create([
                    'product_id'   => $product->id,
                    'type'         => 'out',
                    'quantity'     => $item->qty,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $product->stock,
                    'note'         => "Pre-Order #{$preOrder->po_code}",
                ]);
            }
        }

        return redirect()->route('pre-orders.show', $preOrder->id)
            ->with('success', 'Pengiriman berhasil dipilih. Pre-Order Anda sedang diproses!');
    }

    public function storePayment(Request $request, PreOrder $preOrder)
    {
        if ($preOrder->user_id !== auth()->id()) abort(403);

        if ($preOrder->status !== 'processing') {
            return back()->with('error', 'Pre-Order ini tidak dalam tahap pembayaran.');
        }

        $request->validate([
            'sender_name' => 'nullable|string|max:255',
            'sender_bank' => 'nullable|string|max:100',
            'amount'      => 'nullable|numeric|min:1',
            'pay_date'    => 'nullable|date',
            'proof_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Delete old proof if exists
        if ($preOrder->payment_proof) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($preOrder->payment_proof);
        }

        $path = $request->file('proof_image')->store('po-payment-proofs', 'public');

        $preOrder->update([
            'payment_proof'       => $path,
            'payment_sender_name' => $request->sender_name ?? auth()->user()->name,
            'payment_sender_bank' => $request->sender_bank ?? ($preOrder->payment_method === 'qris' ? 'QRIS' : 'Transfer Manual'),
            'payment_amount'      => $request->amount ?? $preOrder->total_amount,
            'payment_date'        => $request->pay_date ?? now()->toDateString(),
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil diupload! Admin akan segera memverifikasi.');
    }

    public function cancel(PreOrder $preOrder)
    {
        if ($preOrder->user_id !== auth()->id()) abort(403);
        if (!in_array($preOrder->status, ['pending', 'accepted'])) {
            return back()->withErrors(['status' => 'PO tidak bisa dibatalkan pada status ini.']);
        }

        $preOrder->update(['status' => 'cancelled']);

        return back()->with('success', 'Pre-Order berhasil dibatalkan.');
    }
}
