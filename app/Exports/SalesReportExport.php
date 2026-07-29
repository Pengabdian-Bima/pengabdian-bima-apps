<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\PreOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping
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

        $orders = Order::with(['user', 'items.product'])
            ->where('status', 'selesai')
            ->where('created_at', '>=', $startDate)
            ->get();

        $preOrders = PreOrder::with(['user', 'items.product'])
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->get();

        return $orders->concat($preOrders)->sortByDesc('created_at');
    }

    public function headings(): array
    {
        return ['No', 'Kode Pesanan', 'Pelanggan', 'Tanggal', 'Total', 'Status'];
    }

    public function map($order): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $order->order_code ?? $order->po_code,
            $order->user->name,
            $order->created_at->format('d/m/Y'),
            $order->total_amount,
            $order->status_label,
        ];
    }
}
