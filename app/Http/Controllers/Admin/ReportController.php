<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\BestSellingExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->period ?? 'monthly';
        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'yearly' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $completedOrders = Order::where('status', 'selesai')->where('created_at', '>=', $startDate);
        $totalSales = (clone $completedOrders)->sum('total_amount');
        $totalCost = OrderItem::whereHas('order', fn($q) => $q->where('status', 'selesai')->where('created_at', '>=', $startDate))
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(order_items.qty * products.cost_price) as total_cost')->value('total_cost') ?? 0;
        $profit = $totalSales - $totalCost;
        $orderCount = (clone $completedOrders)->count();

        $bestSelling = OrderItem::whereHas('order', fn($q) => $q->where('status', 'selesai')->where('created_at', '>=', $startDate))
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')->with('product:id,name')->orderByDesc('total_qty')->take(10)->get()
            ->map(fn($item) => [
                'product_name' => $item->product->name,
                'total_qty' => $item->total_qty,
                'total_revenue' => $item->total_revenue,
            ]);

        $salesData = Order::where('status', 'selesai')->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')->orderBy('date')->get();

        return Inertia::render('Admin/Reports/Index', [
            'stats' => compact('totalSales', 'totalCost', 'profit', 'orderCount'),
            'bestSelling' => $bestSelling,
            'salesData' => $salesData,
            'period' => $period,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $period = $request->period ?? 'monthly';
        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'yearly' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $orders = Order::with(['items.product', 'user'])->where('status', 'selesai')->where('created_at', '>=', $startDate)->get();
        $totalSales = $orders->sum('total_amount');

        $pdf = Pdf::loadView('reports.sales-pdf', compact('orders', 'totalSales', 'period'));
        return $pdf->download('laporan-penjualan-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $period = $request->period ?? 'monthly';
        return Excel::download(new SalesReportExport($period), 'laporan-penjualan-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportBestSellingPdf(Request $request)
    {
        $period = $request->period ?? 'monthly';
        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'yearly' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $bestSelling = OrderItem::whereHas('order', fn($q) => $q->where('status', 'selesai')->where('created_at', '>=', $startDate))
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')->with('product:id,name')->orderByDesc('total_qty')->get()
            ->map(fn($item) => ['product_name' => $item->product->name, 'total_qty' => $item->total_qty, 'total_revenue' => $item->total_revenue]);

        $pdf = Pdf::loadView('reports.best-selling-pdf', compact('bestSelling', 'period'));
        return $pdf->download('produk-terlaris-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportBestSellingExcel(Request $request)
    {
        $period = $request->period ?? 'monthly';
        return Excel::download(new BestSellingExport($period), 'produk-terlaris-' . now()->format('Y-m-d') . '.xlsx');
    }
}
