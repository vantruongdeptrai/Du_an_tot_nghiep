<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    //Thống kê doanh thu thèo ngày
    public function revenueByDay(Request $request)
    {
        $date = $request->input('date');

        $totalRevenue = Order::whereDate('created_at', $date)
            ->sum('total_price');

        return response()->json([
            'date' => $date,
            'total_revenue' => $totalRevenue,
        ]);
    }
    //Thống kê doanh thu theo tháng
    public function revenueByMonths(Request $request)
{
    $year = $request->input('year');

    if (!$year) {
        return response()->json([
            'message' => 'The year parameter is required. Please provide a valid year.',
        ], 400);
    }

    try {
        $revenues = [];
        for ($month = 1; $month <= 12; $month++) {
            $revenue = Order::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('total_price');

            $revenues[] = [
                'month' => $month,
                'revenue' => $revenue,
            ];
        }

        return response()->json([
            'year' => $year,
            'revenues' => $revenues,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while calculating revenue.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

//Thống kê doanh thu theo năm
    public function revenueByYear(Request $request)
    {
        $year = $request->input('year');

        $totalRevenue = Order::whereYear('created_at', $year)
            ->sum('total_price');

        return response()->json([
            'year' => $year,
            'total_revenue' => $totalRevenue,
        ]);
    }
    //Thống kê tổng doanh thu
    public function getTotalRevenue()
    {
       
        $totalRevenue = Order::sum('total_price'); 

        return response()->json([
            'total_revenue' => $totalRevenue
        ]);
    }
    public function getRevenueByCategory()
    {
        // Truy vấn thống kê doanh thu theo danh mục
        $revenues = DB::table('categories as c')
            ->join('products as p', 'c.id', '=', 'p.category_id')
            ->join('product_variants as pv', 'p.id', '=', 'pv.product_id')
            ->join('order_items as oi', 'pv.id', '=', 'oi.product_variant_id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->select(
                'c.name as category_name',
                DB::raw('SUM(oi.quantity * pv.price) as total_revenue')
            )
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('total_revenue')
            ->get();

        // Trả về dữ liệu
        return response()->json([
            'success' => true,
            'data' => $revenues,
        ]);
    }
    //Thống kê số lượng sản phẩm đã bán
    public function getSoldProductsCount()
    {
        try {
            $statistics = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->where('orders.status_order', 'Giao hàng thành công') 
                ->select(
                    'products.id',
                    'products.name',
                    DB::raw('SUM(order_items.quantity) as total_sold')
                )
                ->groupBy('products.id', 'products.name')
                ->get();
            return response()->json([
                'success' => true,
                'data' => $statistics,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getOrderStats()
    {
        $stats = DB::table('orders')
            ->select('status_order', DB::raw('count(*) as total'))
            ->groupBy('status_order')
            ->get();

        return response()->json($stats);
    }
    
}
