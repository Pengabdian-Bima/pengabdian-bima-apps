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
        $completedPreOrders = PreOrder::where('status', 'completed');
        if ($startDate) {
            $completedOrders->where('created_at', '>=', $startDate);
            $completedPreOrders->where('created_at', '>=', $startDate);
        }

        $totalSales = (clone $completedOrders)->sum('total_amount') + (clone $completedPreOrders)->sum('total_amount');
        
        $orderItemsQuery = OrderItem::whereHas('order', function($q) use ($startDate) {
            $q->where('status', 'selesai');
            if ($startDate) {
                $q->where('created_at', '>=', $startDate);
            }
        });

        $preOrderItemsQuery = PreOrderItem::whereHas('preOrder', function($q) use ($startDate) {
            $q->where('status', 'completed');
            if ($startDate) {
                $q->where('created_at', '>=', $startDate);
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
        $salesDataOrders = $salesDataOrdersQuery
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')->get();

        $salesDataPreOrders = $salesDataPreOrdersQuery
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count')
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

        $ordersQuery = Order::with(['items.product', 'user'])->where('status', 'selesai');
        $preOrdersQuery = PreOrder::with(['items.product', 'user'])->where('status', 'completed');
        if ($startDate) {
            $ordersQuery->where('created_at', '>=', $startDate);
            $preOrdersQuery->where('created_at', '>=', $startDate);
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
        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'yearly' => now()->startOfYear(),
            'all' => null,
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

        $bestSelling = collect(array_values($merged))->sortByDesc('total_qty')->values();

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

            $preOrderQuery = PreOrderItem::where('product_id', $p->id)
                ->whereHas('preOrder', function ($q) use ($startDate) {
                    $q->where('status', 'completed');
                    if ($startDate) {
                        $q->where('created_at', '>=', $startDate);
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
