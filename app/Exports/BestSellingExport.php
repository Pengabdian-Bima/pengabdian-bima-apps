<?php

namespace App\Exports;

use App\Models\OrderItem;
use App\Models\PreOrderItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BestSellingExport implements FromCollection, WithHeadings
{
    protected $period;

    public function __construct($period = 'monthly')
    {
        $this->period = $period;
    }

    public function collection()
    {
        $startDate = match ($this->period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'yearly' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $orderItems = OrderItem::whereHas('order', fn($q) => $q->where('status', 'selesai')->where('created_at', '>=', $startDate))
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->with('product:id,name')
            ->get();

        $preOrderItems = PreOrderItem::whereHas('preOrder', fn($q) => $q->where('status', 'completed')->where('created_at', '>=', $startDate))
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->with('product:id,name')
            ->get();

        $merged = [];
        foreach ($orderItems as $item) {
            $merged[$item->product_id] = [
                'product_name' => $item->product?->name ?? 'Produk Dihapus',
                'total_qty' => $item->total_qty,
                'total_revenue' => $item->total_revenue,
            ];
        }

        foreach ($preOrderItems as $item) {
            if (isset($merged[$item->product_id])) {
                $merged[$item->product_id]['total_qty'] += $item->total_qty;
                $merged[$item->product_id]['total_revenue'] += $item->total_revenue;
            } else {
                $merged[$item->product_id] = [
                    'product_name' => $item->product?->name ?? 'Produk Dihapus',
                    'total_qty' => $item->total_qty,
                    'total_revenue' => $item->total_revenue,
                ];
            }
        }

        return collect(array_values($merged))
            ->sortByDesc('total_qty')
            ->map(fn($item) => [
                'Nama Produk' => $item['product_name'],
                'Jumlah Terjual' => $item['total_qty'],
                'Total Pendapatan' => $item['total_revenue'],
            ]);
    }

    public function headings(): array
    {
        return ['Nama Produk', 'Jumlah Terjual', 'Total Pendapatan'];
    }
}
