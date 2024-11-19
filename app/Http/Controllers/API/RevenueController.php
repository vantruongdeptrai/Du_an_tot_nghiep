<?php

namespace App\Http\Controllers\Api;

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
    
}
