<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\OrderItem;
use App\Models\PreOrderItem;
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
            case 'all':
                $startDate = null;
                $endDate = null;
                break;
            default:
                $startDate = \Carbon\Carbon::now($tz)->startOfMonth()->startOfDay()->setTimezone('UTC');
                $endDate = \Carbon\Carbon::now($tz)->endOfMonth()->endOfDay()->setTimezone('UTC');
                break;
        }

        $products = Product::with('category')->get();

        return $products->map(function ($p) use ($startDate, $endDate) {
            $query = OrderItem::where('product_id', $p->id)
                ->whereHas('order', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'selesai');
                    if ($startDate) {
                        $q->where('created_at', '>=', $startDate);
                    }
                    if ($endDate) {
                        $q->where('created_at', '<=', $endDate);
                    }
                });

            $preOrderQuery = PreOrderItem::where('product_id', $p->id)
                ->whereHas('preOrder', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed');
                    if ($startDate) {
                        $q->where('created_at', '>=', $startDate);
                    }
                    if ($endDate) {
                        $q->where('created_at', '<=', $endDate);
                    }
                });

            $totalQty = (clone $query)->sum('qty') + (clone $preOrderQuery)->sum('qty');
            $totalRevenue = (clone $query)->sum('subtotal') + (clone $preOrderQuery)->sum('subtotal');
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
                $event->sheet->getDelegate()->getStyle('A1:K1')->getFont()->setBold(true);
            },
        ];
    }
}
