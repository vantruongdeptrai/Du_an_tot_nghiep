<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RevenueController extends Controller
{
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
}
