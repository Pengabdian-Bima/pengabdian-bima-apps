<?php

namespace App\Exports;

use App\Models\OrderItem;
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

        return OrderItem::whereHas('order', fn($q) => $q->where('status', 'selesai')->where('created_at', '>=', $startDate))
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->with('product:id,name')
            ->orderByDesc('total_qty')
            ->get()
            ->map(fn($item) => [
                'Nama Produk' => $item->product->name,
                'Jumlah Terjual' => $item->total_qty,
                'Total Pendapatan' => $item->total_revenue,
            ]);
    }

    public function headings(): array
    {
        return ['Nama Produk', 'Jumlah Terjual', 'Total Pendapatan'];
    }
}
