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
    public function revenueByMonth(Request $request)
    {
        $month = $request->input('month');

        $totalRevenue = Order::whereMonth('created_at', Carbon::parse($month)->month)
            ->whereYear('created_at', Carbon::parse($month)->year)
            ->sum('total_price');

        return response()->json([
            'month' => $month,
            'total_revenue' => $totalRevenue,
        ]);
    }
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
    
    public function getTotalRevenue()
    {
       
        $totalRevenue = Order::sum('total_price'); 

        return response()->json([
            'total_revenue' => $totalRevenue
        ]);
    }
}