<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Tổng quan (4 KPI chính)
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
        $totalOrders = Order::count();
        $totalUsers = User::count();
        $totalBooksSold = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->sum('order_items.quantity');
        
        $totalBooks = Book::count(); // Để fallback nếu cần

        // 2. Thống kê đơn hàng theo trạng thái
        $ordersByStatus = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // 3. Doanh thu 7 ngày gần nhất
        $revenue7Days = Order::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
            
        // Tạo mảng 7 ngày liên tục kể cả ngày không có doanh thu
        $chartDates = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
            $displayDate = Carbon::now()->subDays($i)->format('d/m');
            $chartDates[] = $displayDate;
            
            $found = $revenue7Days->firstWhere('date', $dateStr);
            $chartData[] = $found ? $found->total : 0;
        }

        // 4. Top 5 sách bán chạy
        $topBooks = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('books', 'order_items.book_id', '=', 'books.id')
            ->where('orders.status', 'completed')
            ->select('books.title', 'books.cover_image', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('books.id', 'books.title', 'books.cover_image')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // 5. Khách hàng mua nhiều nhất (Top 5)
        $topCustomers = User::withCount(['orders' => function($query) {
                $query->where('status', 'completed');
            }])
            ->withSum(['orders' => function($query) {
                $query->where('status', 'completed');
            }], 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue', 'totalOrders', 'totalUsers', 'totalBooksSold', 'totalBooks',
            'ordersByStatus', 'chartDates', 'chartData', 'topBooks', 'topCustomers'
        ));
    }

    public function exportRevenueCsv()
    {
        $fileName = 'doanh_thu_'.date('Ymd_His').'.csv';
        $orders = Order::where('status', 'completed')
            ->with(['user', 'orderItems.book'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Ma_Don_Hang', 'Khach_Hang', 'Ngay_Mua', 'Tong_Tien', 'Trang_Thai'];

        $callback = function() use($orders, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel to read UTF-8 properly
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $row['Ma_Don_Hang']  = $order->order_number;
                $row['Khach_Hang']    = $order->customer_info['name'] ?? 'Khách vãng lai';
                $row['Ngay_Mua']      = $order->created_at->format('d/m/Y H:i');
                $row['Tong_Tien']     = $order->total_amount;
                $row['Trang_Thai']    = 'Hoàn thành';

                fputcsv($file, array($row['Ma_Don_Hang'], $row['Khach_Hang'], $row['Ngay_Mua'], $row['Tong_Tien'], $row['Trang_Thai']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
