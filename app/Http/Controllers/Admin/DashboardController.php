<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', $this->getDashboardData());
    }

    /**
     * JSON endpoint for real-time polling from the frontend.
     */
    public function realtime()
    {
        return response()->json($this->getDashboardData());
    }

    private function getDashboardData(): array
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalSales = Order::where('status', 'selesai')->sum('total_amount');
        $totalUsers = User::where('role', 'user')->count();
        $pendingPayments = Order::where('status', 'menunggu_verifikasi')->count();
        $lowStockProducts = Product::where('stock', '<=', DB::raw('min_stock'))->count();

        // Sales chart data (last 30 days)
        $salesChart = Order::where('status', 'selesai')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent orders
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'user_name' => $order->user->name,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'status_color' => $order->status_color,
                'created_at' => $order->created_at->format('d M Y H:i'),
            ]);

        // Monthly sales for chart
        $monthlySales = Order::where('status', 'selesai')
            ->where('created_at', '>=', now()->startOfYear())
            ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = $monthlySales[$i] ?? 0;
        }

        return [
            'stats' => [
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
                'totalSales' => $totalSales,
                'totalUsers' => $totalUsers,
                'pendingPayments' => $pendingPayments,
                'lowStockProducts' => $lowStockProducts,
            ],
            'salesChart' => $salesChart,
            'recentOrders' => $recentOrders,
            'monthlyData' => $monthlyData,
        ];
    }
}
