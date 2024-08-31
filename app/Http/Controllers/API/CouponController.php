<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::all();
        return response()->json($coupons, 200);
    }

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

    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);
        return response()->json($coupon, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'discount_amount' => 'sometimes|required|numeric',
            'min_order_value' => 'sometimes|required|numeric',
            'usage_limit' => 'sometimes|required|integer',
            'is_active' => 'sometimes|required|boolean',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
        ]);

        $coupon = Coupon::findOrFail($id);
        $coupon->update($request->all());

        return response()->json($coupon, 200);
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json(null, 204);
    }
}