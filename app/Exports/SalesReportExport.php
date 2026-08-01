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

        $ordersQuery = Order::with(['user', 'items.product'])->where('status', 'selesai');
        $preOrdersQuery = PreOrder::with(['user', 'items.product'])->where('status', 'completed');

        if ($startDate) {
            $ordersQuery->where('created_at', '>=', $startDate);
            $preOrdersQuery->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $ordersQuery->where('created_at', '<=', $endDate);
            $preOrdersQuery->where('created_at', '<=', $endDate);
        }

        $orders = $ordersQuery->get();
        $preOrders = $preOrdersQuery->get();

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
