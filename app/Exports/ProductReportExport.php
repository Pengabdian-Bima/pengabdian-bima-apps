<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\OrderItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductReportExport implements FromCollection, WithHeadings, WithMapping, WithEvents
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
            'all' => null,
            default => now()->startOfMonth(),
        };

        $products = Product::with('category')->get();

        return $products->map(function ($p) use ($startDate) {
            $query = OrderItem::where('product_id', $p->id)
                ->whereHas('order', function ($q) use ($startDate) {
                    $q->where('status', 'selesai');
                    if ($startDate) {
                        $q->where('created_at', '>=', $startDate);
                    }
                });

            $totalQty = (clone $query)->sum('qty') ?? 0;
            $totalRevenue = (clone $query)->sum('subtotal') ?? 0;
            $totalCost = $totalQty * $p->cost_price;
            $netProfit = $totalRevenue - $totalCost;
            $margin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

            return [
                'name' => $p->name,
                'category' => $p->category?->name ?? '-',
                'price' => $p->price,
                'cost_price' => $p->cost_price,
                'stock' => $p->stock,
                'min_stock' => $p->min_stock,
                'total_qty' => $totalQty,
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'net_profit' => $netProfit,
                'margin' => $margin,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Produk',
            'Kategori',
            'Harga Jual (Rp)',
            'Harga Modal (Rp)',
            'Sisa Stok',
            'Total Terjual (Pcs)',
            'Total Omset (Rp)',
            'Total Modal (Rp)',
            'Laba Bersih (Rp)',
            'Margin Profit (%)',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row['name'],
            $row['category'],
            $row['price'],
            $row['cost_price'],
            $row['stock'],
            $row['total_qty'],
            $row['total_revenue'],
            $row['total_cost'],
            $row['net_profit'],
            $row['margin'] . '%',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Formatting title & summary if needed
                $event->sheet->getDelegate()->getStyle('A1:K1')->getFont()->setBold(true);
            },
        ];
    }
}
