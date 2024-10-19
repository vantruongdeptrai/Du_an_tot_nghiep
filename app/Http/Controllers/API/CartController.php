<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;
class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function addToCart(){
    
}


//Hiển thị giỏ hàng cho người đã đăng nhập
 public function showCartUser(Request $request)
 {
     $user = $request->user(); //Lấy user từ request Sanctum token

     //Lấy các sản phẩm trong giỏ hàng của người dùng
     $carts = Cart::with('productVariant')
                    ->where('user_id', $user->id)
                    ->where('quantity', '>', 1) // Chỉ lấy sản phẩm có số lượng > 1
                    ->get();
    //Tính tổng tiền giỏ hàng
    $totalPrice = $carts->sum(function($item) {
        return $item->quantity * $item->price;
    });

     if ($carts->isEmpty()) {
         return response()->json(['message' => 'Giỏ hàng trống'], 200);
     }
    //Trả về giỏ hàng và tổng tiền
     return response()->json([
        'cart' => $carts,
        'total_price' => $totalPrice]);
 }


 //Hiển thị giỏ hàng cho người chưa đăng nhập
 public function showCartGuest(Request $request)
    {
        $cartToken = $request->header('X-Cart-Token'); // Nhận token từ header

        if (!$cartToken) {
            return response()->json(['error' => 'No cart token'], 400);
        }

        // Lấy các sản phẩm trong giỏ hàng dựa trên token tạm thời
        $carts = Cart::with('productVariant')
                    ->where('guest_token', $cartToken)
                    ->where('quantity', '>', 1) // Chỉ lấy sản phẩm có số lượng > 1
                    ->get();
        //Tính tổng tiền giỏ hàng
    $totalPrice = $carts->sum(function($item) {
        return $item->quantity * $item->price;
    });

        if ($carts->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng trống'], 200);
        }

        
       //Trả về giỏ hàng và tổng tiền
     return response()->json([
        'cart' => $carts,
        'total_price' => $totalPrice]);
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
