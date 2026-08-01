<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PreOrder;
use App\Models\PreOrderItem;
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
    private function getDateRange(Request $request): array
    {
        $startDate = null;
        $endDate = null;
        $tz = 'Asia/Makassar';

        if ($request->filled('start_date') || $request->filled('end_date')) {
            if ($request->filled('start_date')) {
                $startDate = \Carbon\Carbon::parse($request->start_date, $tz)->startOfDay()->setTimezone('UTC');
            }
            if ($request->filled('end_date')) {
                $endDate = \Carbon\Carbon::parse($request->end_date, $tz)->endOfDay()->setTimezone('UTC');
            }
            return [$startDate, $endDate];
        }

        $period = $request->period ?? 'monthly';
        switch ($period) {
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

        return [$startDate, $endDate];
    }

    public function index(Request $request)
    {
        $period = $request->period ?? 'monthly';
        [$startDate, $endDate] = $this->getDateRange($request);

        $completedOrders = Order::where('status', 'selesai');
        $completedPreOrders = PreOrder::where('status', 'completed');
        if ($startDate) {
            $completedOrders->where('created_at', '>=', $startDate);
            $completedPreOrders->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $completedOrders->where('created_at', '<=', $endDate);
            $completedPreOrders->where('created_at', '<=', $endDate);
        }

        $totalSales = (clone $completedOrders)->sum('total_amount') + (clone $completedPreOrders)->sum('total_amount');
        
        $orderItemsQuery = OrderItem::whereHas('order', function($q) use ($startDate, $endDate) {
            $q->where('status', 'selesai');
            if ($startDate) {
                $q->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $q->where('created_at', '<=', $endDate);
            }
        });

        $preOrderItemsQuery = PreOrderItem::whereHas('preOrder', function($q) use ($startDate, $endDate) {
            $q->where('status', 'completed');
            if ($startDate) {
                $q->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $q->where('created_at', '<=', $endDate);
            }
        });

        $totalOrderCost = (clone $orderItemsQuery)
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(order_items.qty * products.cost_price) as total_cost')->value('total_cost') ?? 0;

        $totalPreOrderCost = (clone $preOrderItemsQuery)
            ->join('products', 'pre_order_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(pre_order_items.qty * products.cost_price) as total_cost')->value('total_cost') ?? 0;
            
        $totalCost = $totalOrderCost + $totalPreOrderCost;
        $profit = $totalSales - $totalCost;
        $orderCount = (clone $completedOrders)->count() + (clone $completedPreOrders)->count();

        // Best Selling combining both
        $bestSellingOrders = (clone $orderItemsQuery)
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->get();

        $bestSellingPreOrders = (clone $preOrderItemsQuery)
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->get();

        $mergedBest = [];
        foreach ($bestSellingOrders as $item) {
            $mergedBest[$item->product_id] = [
                'product_id' => $item->product_id,
                'total_qty' => (int)$item->total_qty,
                'total_revenue' => (float)$item->total_revenue,
            ];
        }
        foreach ($bestSellingPreOrders as $item) {
            if (isset($mergedBest[$item->product_id])) {
                $mergedBest[$item->product_id]['total_qty'] += (int)$item->total_qty;
                $mergedBest[$item->product_id]['total_revenue'] += (float)$item->total_revenue;
            } else {
                $mergedBest[$item->product_id] = [
                    'product_id' => $item->product_id,
                    'total_qty' => (int)$item->total_qty,
                    'total_revenue' => (float)$item->total_revenue,
                ];
            }
        }

        $productsMap = Product::whereIn('id', array_keys($mergedBest))->get()->keyBy('id');
        $bestSelling = collect($mergedBest)
            ->map(function($item) use ($productsMap) {
                return [
                    'product_name' => $productsMap->get($item['product_id'])?->name ?? 'Produk Dihapus',
                    'total_qty' => $item['total_qty'],
                    'total_revenue' => $item['total_revenue'],
                ];
            })
            ->sortByDesc('total_qty')
            ->take(10)
            ->values();

        // Combine Sales chart data
        $salesDataOrdersQuery = Order::where('status', 'selesai');
        $salesDataPreOrdersQuery = PreOrder::where('status', 'completed');
        if ($startDate) {
            $salesDataOrdersQuery->where('created_at', '>=', $startDate);
            $salesDataPreOrdersQuery->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $salesDataOrdersQuery->where('created_at', '<=', $endDate);
            $salesDataPreOrdersQuery->where('created_at', '<=', $endDate);
        }
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        $dateExpr = 'DATE(created_at)';
        if ($driver === 'mysql') {
            $dateExpr = 'DATE(CONVERT_TZ(created_at, "+00:00", "+08:00"))';
        } elseif ($driver === 'sqlite') {
            $dateExpr = 'DATE(datetime(created_at, "+8 hours"))';
        }

        $salesDataOrders = $salesDataOrdersQuery
            ->selectRaw($dateExpr . ' as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')->get();

        $salesDataPreOrders = $salesDataPreOrdersQuery
            ->selectRaw($dateExpr . ' as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')->get();

        $mergedSales = [];
        foreach ($salesDataOrders as $sd) {
            $mergedSales[$sd->date] = [
                'date' => $sd->date,
                'total' => (float)$sd->total,
                'count' => (int)$sd->count,
            ];
        }
        foreach ($salesDataPreOrders as $sd) {
            if (isset($mergedSales[$sd->date])) {
                $mergedSales[$sd->date]['total'] += (float)$sd->total;
                $mergedSales[$sd->date]['count'] += (int)$sd->count;
            } else {
                $mergedSales[$sd->date] = [
                    'date' => $sd->date,
                    'total' => (float)$sd->total,
                    'count' => (int)$sd->count,
                ];
            }
        }
        $salesData = collect($mergedSales)->sortBy('date')->values();

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
        $reportData = $this->getProductReportData($request);

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
        $reportData = $this->getProductReportData($request);

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
        [$startDate, $endDate] = $this->getDateRange($request);

        $ordersQuery = Order::with(['items.product', 'user'])->where('status', 'selesai');
        $preOrdersQuery = PreOrder::with(['items.product', 'user'])->where('status', 'completed');
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

        // Convert preOrders code key so view behaves nicely
        $mappedPreOrders = $preOrders->map(function($po) {
            $po->order_code = $po->po_code; // alias order_code to po_code
            return $po;
        });

        $combined = $orders->concat($mappedPreOrders)->sortByDesc('created_at');
        $totalSales = $combined->sum('total_amount');

        $pdf = Pdf::loadView('reports.sales-pdf', [
            'orders' => $combined,
            'totalSales' => $totalSales,
            'period' => $period
        ]);
        
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
        [$startDate, $endDate] = $this->getDateRange($request);

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

        $bestSelling = collect(array_values($merged))->sortByDesc('total_qty')->values();

        $pdf = Pdf::loadView('reports.best-selling-pdf', compact('bestSelling', 'period'));
        return $pdf->download('produk-terlaris-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportBestSellingExcel(Request $request)
    {
        $period = $request->period ?? 'monthly';
        return Excel::download(new BestSellingExport($period), 'produk-terlaris-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function getProductReportData(Request $request)
    {
        $period = $request->period ?? 'monthly';
        [$startDate, $endDate] = $this->getDateRange($request);

        $tz = 'Asia/Makassar';
        $periodLabel = match ($period) {
            'daily' => 'Hari Ini (' . \Carbon\Carbon::now($tz)->format('d M Y') . ')',
            'weekly' => 'Minggu Ini (' . \Carbon\Carbon::now($tz)->startOfWeek()->format('d M Y') . ' - ' . \Carbon\Carbon::now($tz)->endOfWeek()->format('d M Y') . ')',
            'monthly' => 'Bulan Ini (' . \Carbon\Carbon::now($tz)->format('F Y') . ')',
            'yearly' => 'Tahun Ini (' . \Carbon\Carbon::now($tz)->format('Y') . ')',
            'all' => 'Semua Waktu',
            default => 'Bulan Ini (' . \Carbon\Carbon::now($tz)->format('F Y') . ')',
        };

        if ($request->filled('start_date') || $request->filled('end_date')) {
            $periodLabel = 'Periode Custom (' . ($startDate ? $startDate->format('d M Y') : 'Awal') . ' - ' . ($endDate ? $endDate->format('d M Y') : 'Kini') . ')';
        }

        $products = Product::with('category')->get();

        $productData = $products->map(function ($p) use ($startDate, $endDate) {
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

        // Compute Conclusion
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
