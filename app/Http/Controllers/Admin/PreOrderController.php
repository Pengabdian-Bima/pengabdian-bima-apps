<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PreOrder;
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

    public function index(Request $request)
    {
        $query = PreOrder::with('user');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $preOrders = $query->latest()->paginate(15)->withQueryString()->through(fn ($po) => [
            'id'           => $po->id,
            'po_code'      => $po->po_code,
            'user_name'    => $po->user->name,
            'total_amount' => $po->total_amount,
            'status'       => $po->status,
            'status_label' => $po->status_label,
            'status_color' => $po->status_color,
            'created_at'   => $po->created_at->format('d M Y H:i'),
        ]);

        return Inertia::render('Admin/PreOrders/Index', [
            'preOrders'     => $preOrders,
            'currentStatus' => $request->status,
            'pendingCount'  => PreOrder::where('status', 'pending')->count(),
        ]);
    }

    public function show(PreOrder $preOrder)
    {
        $preOrder->load(['user', 'items.product']);

        return Inertia::render('Admin/PreOrders/Show', [
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
                'user'                 => [
                    'name'  => $preOrder->user->name,
                    'email' => $preOrder->user->email,
                    'phone' => $preOrder->user->phone,
                ],
                'items' => $preOrder->items->map(fn ($item) => [
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

    public function accept(Request $request, PreOrder $preOrder)
    {
        if ($preOrder->status !== 'pending') {
            return back()->withErrors(['status' => 'PO ini sudah tidak dalam status menunggu.']);
        }

        $request->validate([
            'estimated_days' => 'required|integer|min:1|max:365',
        ]);

        $preOrder->update([
            'status'          => 'accepted',
            'estimated_days'  => $request->estimated_days,
            'rejection_reason' => null,
        ]);

        $this->fonnteService->sendCustomerStatusNotification(
            $preOrder,
            'DITERIMA',
            "Pre-Order Anda telah disetujui! Estimasi pengerjaan: {$request->estimated_days} hari. Silakan lanjutkan ke pemrosesan pengiriman & pembayaran di aplikasi."
        );

        return back()->with('success', 'Pre-Order berhasil diterima. Pelanggan akan segera dihubungi.');
    }

    public function reject(Request $request, PreOrder $preOrder)
    {
        if ($preOrder->status !== 'pending') {
            return back()->withErrors(['status' => 'PO ini sudah tidak dalam status menunggu.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $preOrder->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        $this->fonnteService->sendCustomerStatusNotification(
            $preOrder,
            'DITOLAK',
            "Alasan penolakan: {$request->rejection_reason}"
        );

        return back()->with('success', 'Pre-Order berhasil ditolak.');
    }

    public function complete(PreOrder $preOrder)
    {
        if (!in_array($preOrder->status, ['processing'])) {
            return back()->with('error', 'PO tidak dapat diselesaikan pada status ini.');
        }

        $preOrder->update(['status' => 'completed']);

        $this->fonnteService->sendCustomerStatusNotification(
            $preOrder,
            'SELESAI',
            "Pesanan Pre-Order Anda telah selesai diproses dan dikirim. Terima kasih telah berbelanja!"
        );

        return back()->with('success', 'Pre-Order berhasil ditandai selesai!');
    }
}
