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
use App\Exports\ProductReportExport;

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
            'all' => null,
            default => now()->startOfMonth(),
        };

        $completedOrders = Order::where('status', 'selesai');
        if ($startDate) {
            $completedOrders->where('created_at', '>=', $startDate);
        }

        $totalSales = (clone $completedOrders)->sum('total_amount');
        
        $orderItemsQuery = OrderItem::whereHas('order', function($q) use ($startDate) {
            $q->where('status', 'selesai');
            if ($startDate) {
                $q->where('created_at', '>=', $startDate);
            }
        });

        $totalCost = (clone $orderItemsQuery)
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(order_items.qty * products.cost_price) as total_cost')->value('total_cost') ?? 0;
            
        $profit = $totalSales - $totalCost;
        $orderCount = (clone $completedOrders)->count();

        $bestSelling = (clone $orderItemsQuery)
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')->with('product:id,name')->orderByDesc('total_qty')->take(10)->get()
            ->map(fn($item) => [
                'product_name' => $item->product?->name ?? 'Produk Dihapus',
                'total_qty' => $item->total_qty,
                'total_revenue' => $item->total_revenue,
            ]);

        $salesDataQuery = Order::where('status', 'selesai');
        if ($startDate) {
            $salesDataQuery->where('created_at', '>=', $startDate);
        }
        $salesData = $salesDataQuery
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')->orderBy('date')->get();

        return Inertia::render('Admin/Reports/Index', [
            'stats' => compact('totalSales', 'totalCost', 'profit', 'orderCount'),
            'bestSelling' => $bestSelling,
            'salesData' => $salesData,
            'period' => $period,
        ]);
    }

    public function productReport(Request $request)
    {
        $period = $request->period ?? 'monthly';
        $reportData = $this->getProductReportData($period);

        return Inertia::render('Admin/Reports/Products', [
            'products' => $reportData['products'],
            'conclusion' => $reportData['conclusion'],
            'period' => $period,
            'periodLabel' => $reportData['periodLabel'],
        ]);
    }

    public function exportProductReportPdf(Request $request)
    {
        $period = $request->period ?? 'monthly';
        $reportData = $this->getProductReportData($period);

        $pdf = Pdf::loadView('reports.product-report-pdf', [
            'products' => $reportData['products'],
            'conclusion' => $reportData['conclusion'],
            'period' => $period,
            'periodLabel' => $reportData['periodLabel'],
        ]);

        return $pdf->download('laporan-per-produk-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportProductReportExcel(Request $request)
    {
        $period = $request->period ?? 'monthly';
        return Excel::download(new ProductReportExport($period), 'laporan-per-produk-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $period = $request->period ?? 'monthly';
        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'yearly' => now()->startOfYear(),
            'all' => null,
            default => now()->startOfMonth(),
        };

        $query = Order::with(['items.product', 'user'])->where('status', 'selesai');
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        $orders = $query->get();
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
            'all' => null,
            default => now()->startOfMonth(),
        };

        $query = OrderItem::whereHas('order', function($q) use ($startDate) {
            $q->where('status', 'selesai');
            if ($startDate) {
                $q->where('created_at', '>=', $startDate);
            }
        });

        $bestSelling = $query->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')->with('product:id,name')->orderByDesc('total_qty')->get()
            ->map(fn($item) => ['product_name' => $item->product?->name ?? 'Produk Dihapus', 'total_qty' => $item->total_qty, 'total_revenue' => $item->total_revenue]);

        $pdf = Pdf::loadView('reports.best-selling-pdf', compact('bestSelling', 'period'));
        return $pdf->download('produk-terlaris-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportBestSellingExcel(Request $request)
    {
        $period = $request->period ?? 'monthly';
        return Excel::download(new BestSellingExport($period), 'produk-terlaris-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function getProductReportData($period)
    {
        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'yearly' => now()->startOfYear(),
            'all' => null,
            default => now()->startOfMonth(),
        };

        $periodLabel = match ($period) {
            'daily' => 'Hari Ini (' . now()->format('d M Y') . ')',
            'weekly' => 'Minggu Ini (' . now()->startOfWeek()->format('d M Y') . ' - ' . now()->endOfWeek()->format('d M Y') . ')',
            'monthly' => 'Bulan Ini (' . now()->format('F Y') . ')',
            'yearly' => 'Tahun Ini (' . now()->format('Y') . ')',
            'all' => 'Semua Waktu',
            default => 'Bulan Ini (' . now()->format('F Y') . ')',
        };

        $products = Product::with('category')->get();

        $productData = $products->map(function ($p) use ($startDate) {
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
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category?->name ?? '-',
                'price' => (float)$p->price,
                'cost_price' => (float)$p->cost_price,
                'stock' => (int)$p->stock,
                'min_stock' => (int)$p->min_stock,
                'total_qty' => (int)$totalQty,
                'total_revenue' => (float)$totalRevenue,
                'total_cost' => (float)$totalCost,
                'net_profit' => (float)$netProfit,
                'margin' => $margin,
                'thumbnail_url' => $p->thumbnail_url,
            ];
        })->sortByDesc('total_revenue')->values();

        // Compute Conclusion / Kesimpulan Laporan
        $topSeller = (clone $productData)->sortByDesc('total_qty')->first(fn($p) => $p['total_qty'] > 0);
        $topRevenue = (clone $productData)->sortByDesc('total_revenue')->first(fn($p) => $p['total_revenue'] > 0);
        $topProfit = (clone $productData)->sortByDesc('net_profit')->first(fn($p) => $p['net_profit'] > 0);
        $lowStockProducts = (clone $productData)->filter(fn($p) => $p['stock'] <= $p['min_stock'])->values()->all();
        $zeroSalesProducts = (clone $productData)->filter(fn($p) => $p['total_qty'] == 0)->values()->all();

        $totalSalesSum = $productData->sum('total_revenue');
        $totalProfitSum = $productData->sum('net_profit');
        $totalQtySum = $productData->sum('total_qty');

        $summaryText = "Pada periode {$periodLabel}, total penjualan produk mencatatkan omset keseluruhan sebesar Rp " . number_format($totalSalesSum, 0, ',', '.') . " dengan laba bersih Rp " . number_format($totalProfitSum, 0, ',', '.') . " (" . number_format($totalQtySum, 0, ',', '.') . " pcs produk terjual). ";

        if ($topSeller) {
            $summaryText .= "Produk unggulan dengan volume terlaris adalah {$topSeller['name']} ({$topSeller['total_qty']} pcs). ";
        }
        if (count($lowStockProducts) > 0) {
            $summaryText .= "Terdapat " . count($lowStockProducts) . " produk yang membutuhkan tambahan persediaan stok.";
        } else {
            $summaryText .= "Ketersediaan persediaan stok seluruh produk saat ini terpantau stabil.";
        }

        $conclusion = [
            'top_seller' => $topSeller,
            'top_revenue' => $topRevenue,
            'top_profit' => $topProfit,
            'low_stock_products' => $lowStockProducts,
            'zero_sales_products' => $zeroSalesProducts,
            'summary_text' => $summaryText,
            'total_sales' => $totalSalesSum,
            'total_profit' => $totalProfitSum,
            'total_qty' => $totalQtySum,
        ];

        return [
            'products' => $productData,
            'conclusion' => $conclusion,
            'period' => $period,
            'periodLabel' => $periodLabel,
        ];
    }
}
