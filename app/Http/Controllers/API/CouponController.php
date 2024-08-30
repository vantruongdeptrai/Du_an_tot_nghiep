<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // Lấy danh sách tất cả coupons
    public function index()
    {
        return response()->json(Coupon::all());
    }

    // Tạo một coupon mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_amount' => 'required|numeric',
            'min_order_value' => 'required|numeric',
            'usage_limit' => 'required|integer',
            'is_active' => 'required|boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $coupon = Coupon::create($request->all());
        return response()->json($coupon, 201);
    }

    // Lấy một coupon theo ID
    public function show($id)
    {
        $coupon = Coupon::find($id);
        if ($coupon) {
            return response()->json($coupon);
        }
        return response()->json(['message' => 'Coupon not found'], 404);
    }

    // Cập nhật một coupon
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_amount' => 'required|numeric',
            'min_order_value' => 'required|numeric',
            'usage_limit' => 'required|integer',
            'is_active' => 'required|boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $coupon = Coupon::find($id);
        if ($coupon) {
            $coupon->update($request->all());
            return response()->json($coupon);
        }
        return response()->json(['message' => 'Coupon not found'], 404);
    }

    // Xóa một coupon
    public function destroy($id)
    {
        $coupon = Coupon::find($id);
        if ($coupon) {
            $coupon->delete();
            return response()->json(['message' => 'Coupon deleted']);
        }
        return response()->json(['message' => 'Coupon not found'], 404);
    }
}
