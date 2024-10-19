<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function addToCart(){
    
}

public function showCart()
    {
        // Người dùng đã đăng nhập, lấy giỏ hàng của họ
        $carts = Cart::where('user_id', auth()->id())
                     ->with('product', 'productVariant')
                     ->get();

        // Tính tổng tiền giỏ hàng
        $totalPrice = $carts->sum(function($item) {
            return $item->quantity * $item->price;
        });

        // Trả về giỏ hàng và tổng tiền
        return response()->json([
            'cart' => $carts,
            'total_price' => $totalPrice
        ]);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
