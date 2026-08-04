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
        $startDate = null;
        $endDate = null;

        $tz = 'Asia/Makassar';
        switch ($this->period) {
            case 'daily':
                $startDate = \Carbon\Carbon::now($tz)->startOfDay()->setTimezone('UTC');
                $endDate = \Carbon\Carbon::now($tz)->endOfDay()->setTimezone('UTC');
                break;
            case 'weekly':
                $startDate = \Carbon\Carbon::now($tz)->startOfWeek()->startOfDay()->setTimezone('UTC');
                $endDate = \Carbon\Carbon::now($tz)->endOfWeek()->endOfDay()->setTimezone('UTC');
                break;
            case 'monthly':
                $startDate = \Carbon\Carbon::now($tz)->startOfMonth()->startOfDay()->setTimezone('UTC');
                $endDate = \Carbon\Carbon::now($tz)->endOfMonth()->endOfDay()->setTimezone('UTC');
                break;
            case 'yearly':
                $startDate = \Carbon\Carbon::now($tz)->startOfYear()->startOfDay()->setTimezone('UTC');
                $endDate = \Carbon\Carbon::now($tz)->endOfYear()->endOfDay()->setTimezone('UTC');
                break;
            default:
                $startDate = \Carbon\Carbon::now($tz)->startOfMonth()->startOfDay()->setTimezone('UTC');
                $endDate = \Carbon\Carbon::now($tz)->endOfMonth()->endOfDay()->setTimezone('UTC');
                break;
        }

        $orderItems = OrderItem::whereHas('order', function($q) use ($startDate, $endDate) {
            $q->where('status', 'selesai');
            if ($startDate) $q->where('created_at', '>=', $startDate);
            if ($endDate) $q->where('created_at', '<=', $endDate);
        })
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->with('product:id,name')
            ->get();

        $preOrderItems = PreOrderItem::whereHas('preOrder', function($q) use ($startDate, $endDate) {
            $q->where('status', 'completed');
            if ($startDate) $q->where('created_at', '>=', $startDate);
            if ($endDate) $q->where('created_at', '<=', $endDate);
        })
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
