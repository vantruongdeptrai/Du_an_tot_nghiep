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
    public function revenueByDay (Request $request)
    {
        $year = $request->input('year'); // Lấy năm từ request
        $month = $request->input('month'); // Lấy tháng từ request

        // Kiểm tra tham số year và month
        if (!$year || !$month) {
            return response()->json([
                'success' => false,
                'message' => 'Both year and month parameters are required. Please provide valid inputs.',
            ], 400);
        }

        try {
            // Lấy số ngày trong tháng
            $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;

            // Tạo danh sách các ngày trong tháng
            $days = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $days[] = sprintf('%04d-%02d-%02d', $year, $month, $day);
            }

            // Lấy dữ liệu doanh thu từ DB
            $revenuesFromDB = Order::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->selectRaw('DATE(created_at) as date, SUM(total_price) as total_revenue')
                ->groupByRaw('DATE(created_at)')
                ->pluck('total_revenue', 'date');
            $revenues = [];
            foreach ($days as $day) {
                $revenues[] = [
                    'date' => $day,
                    'total_revenue' => $revenuesFromDB[$day] ?? 0, // Nếu không có dữ liệu thì bằng 0
                ];
            }

            return response()->json([
                'success' => true,
                'year' => $year,
                'month' => $month,
                'data' => $revenues,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while calculating revenues.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
    //Thống kê số lượng sản phẩm đã bán .
    public function getSoldProductsCount(Request $request)
{
    try {
        // Lấy tháng và năm từ request
        $year = $request->input('year');
        $month = $request->input('month');

        if (!$year || !$month) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp cả năm và tháng.'
            ], 400);
        }

        // Lấy danh sách ngày trong tháng
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Tạo danh sách ngày từ 1 -> số ngày trong tháng
        $dates = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dates[] = sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        // Lấy dữ liệu thống kê từ database
        $rawData = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('orders.status_order', 'Giao hàng thành công')
            ->whereYear('orders.created_at', $year)
            ->whereMonth('orders.created_at', $month)
            ->select(
                DB::raw('DATE(orders.created_at) as sold_date'),
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('sold_date', 'products.id', 'products.name')
            ->get();

        // Chuyển dữ liệu thành dạng dễ xử lý
        $formattedData = [];
        foreach ($rawData as $row) {
            $formattedData[$row->sold_date][$row->id] = [
                'name' => $row->name,
                'total_sold' => $row->total_sold
            ];
        }

        // Chuẩn bị kết quả cuối cùng
        $results = [];
        foreach ($dates as $date) {
            $products = $formattedData[$date] ?? [];

            $dailyData = [
                'date' => $date,
                'products' => []
            ];

            // Nếu không có sản phẩm nào, tạo giá trị mặc định
            foreach ($products as $id => $product) {
                $dailyData['products'][] = [
                    'id' => $id,
                    'name' => $product['name'],
                    'total_sold' => $product['total_sold']
                ];
            }

            // Nếu không có sản phẩm trong ngày
            if (empty($dailyData['products'])) {
                $dailyData['products'] = [];
            }

            $results[] = $dailyData;
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi xảy ra: ' . $e->getMessage()
        ], 500);
    }
}

    //thống kê theo trạng thái đơn hàng
    public function getOrderStats()
    {
        $stats = DB::table('orders')
            ->select('status_order', DB::raw('count(*) as total'))
            ->groupBy('status_order')
            ->get();

        return response()->json($stats);
    }
    //Thống kê tôgr đơn theo trạng thái đơn hàng theo ngày tuỳ ý
    public function getOrderStatsByDate(Request $request)
{
    // Nhận tháng và năm từ request
    $year = $request->input('year');
    $month = $request->input('month');

    if (!$year || !$month) {
        return response()->json([
            'success' => false,
            'message' => 'Vui lòng cung cấp năm và tháng.'
        ], 400);
    }

    // Lấy danh sách tất cả các ngày trong tháng
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    // Tạo danh sách ngày trong tháng
    $dates = [];
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $dates[] = sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    // Lấy thống kê đơn hàng trong tháng từ database
    $rawStats = DB::table('orders')
        ->select(DB::raw('DATE(created_at) as order_date, status_order, count(*) as total'))
        ->whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->groupBy('order_date', 'status_order')
        ->orderBy('order_date', 'asc')
        ->get();

    // Chuyển đổi dữ liệu thành dạng dễ xử lý
    $formattedData = [];
    foreach ($rawStats as $stat) {
        $formattedData[$stat->order_date][$stat->status_order] = $stat->total;
    }

    // Tạo kết quả trả về với tất cả ngày trong tháng
    $results = [];
    foreach ($dates as $date) {
        $dailyStats = [
            'date' => $date,
            'statuses' => [],
        ];

        // Nếu không có đơn hàng cho ngày này, giá trị mặc định là 0
        $statuses = ['Chờ xac nhận','Đã xác nhận','Đang chuẩn bị','Đang vận chuyển','Giao hàng thành công','Đã huỷ','Chờ xách nhận huỷ']; // Thêm các trạng thái cần thiết

        foreach ($statuses as $status) {
            $dailyStats['statuses'][] = [
                'status' => $status,
                'total' => $formattedData[$date][$status] ?? 0,
            ];
        }

        $results[] = $dailyStats;
    }

    return response()->json([
        'success' => true,
        'data' => $results,
    ]);
}


public function getOrderByDates(Request $request)
{
    // Lấy ngày từ request
    $date = $request->input('date');

    if (!$date) {
        return response()->json([
            'success' => false,
            'message' => 'Vui lòng cung cấp ngày (date).'
        ], 400);
    }

    try {
        // Truy vấn thống kê đơn hàng theo trạng thái trong ngày cụ thể
        $rawStats = DB::table('orders')
            ->select('status_order', DB::raw('count(*) as total'))
            ->whereDate('created_at', $date)
            ->groupBy('status_order')
            ->get();

        // Danh sách các trạng thái cần hiển thị
        $statuses = [
            'Chờ xác nhận',
            'Đã xác nhận',
            'Đang chuẩn bị',
            'Đang vận chuyển',
            'Giao hàng thành công',
            'Đã hủy',
            'Chờ xác nhận hủy',
        ];

        // Định dạng kết quả
        $results = [];
        foreach ($statuses as $status) {
            $results[] = [
                'status' => $status,
                'total' => $rawStats->firstWhere('status_order', $status)->total ?? 0,
            ];
        }

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $results,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
        ], 500);
    }
}


public function revenueBySpecificDate(Request $request)
{
    $date = $request->input('date'); // Lấy ngày từ request

    // Kiểm tra tham số date
    if (!$date) {
        return response()->json([
            'success' => false,
            'message' => 'The date parameter is required. Please provide a valid date (YYYY-MM-DD).',
        ], 400);
    }

    try {
        // Kiểm tra định dạng ngày
        if (!\Carbon\Carbon::createFromFormat('Y-m-d', $date)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format. Please use YYYY-MM-DD.',
            ], 400);
        }

        // Lấy doanh thu của ngày cụ thể
        $totalRevenue = Order::whereDate('created_at', $date)
            ->sum('total_price'); // Tính tổng doanh thu

        return response()->json([
            'success' => true,
            'date' => $date,
            'total_revenue' => $totalRevenue,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while calculating revenue.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function getSoldProductsCountByDay(Request $request)
{
    try {
        // Lấy ngày từ request (dạng YYYY-MM-DD)
        $date = $request->input('date');

        if (!$date) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp ngày (dạng YYYY-MM-DD).'
            ], 400);
        }

        // Kiểm tra định dạng ngày có hợp lệ không
        if (!\Carbon\Carbon::createFromFormat('Y-m-d', $date)) {
            return response()->json([
                'success' => false,
                'message' => 'Định dạng ngày không hợp lệ. Vui lòng sử dụng định dạng YYYY-MM-DD.'
            ], 400);
        }

        // Lấy dữ liệu thống kê từ database cho ngày cụ thể
        $rawData = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('orders.status_order', 'Giao hàng thành công')
            ->whereDate('orders.created_at', $date)  // Lọc theo ngày
            ->select(
                DB::raw('DATE(orders.created_at) as sold_date'),
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('sold_date', 'products.id', 'products.name')
            ->get();

        // Chuyển dữ liệu thành dạng dễ xử lý
        $formattedData = [];
        foreach ($rawData as $row) {
            $formattedData[$row->sold_date][$row->id] = [
                'name' => $row->name,
                'total_sold' => $row->total_sold
            ];
        }

        // Chuẩn bị kết quả trả về
        $dailyData = [
            'date' => $date,
            'products' => []
        ];

        // Nếu có sản phẩm, thêm vào danh sách
        if (isset($formattedData[$date])) {
            foreach ($formattedData[$date] as $id => $product) {
                $dailyData['products'][] = [
                    'id' => $id,
                    'name' => $product['name'],
                    'total_sold' => $product['total_sold']
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $dailyData
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi xảy ra: ' . $e->getMessage()
        ], 500);
    }
}

}
