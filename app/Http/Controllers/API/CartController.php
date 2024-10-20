<?php

namespace App\Http\Controllers\API;

use App\Models\Cart;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function addToCart(Request $request)
    {
        // Logic cho người dùng đã đăng nhập
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);

        $userId = Auth::id(); // Lấy ID người dùng nếu đã đăng nhập
        Cart::updateOrCreate(
            [
                'user_id' => $userId,
                'product_id' => $request->product_id,
                'product_variant_id' => $request->product_variant_id,
            ],
            [
                'quantity' => DB::raw('quantity + ' . $request->quantity),
                'price' => $request->price,
            ]
        );

        return response()->json(['message' => 'Product added to cart successfully.']);
    }

    public function addToCartGuest(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);

        // Lấy session ID để xác định khách hàng
        $sessionId = $request->session()->getId();

        // Lấy giỏ hàng từ session (giỏ hàng này thuộc về session ID của người dùng)
        $cart = $request->session()->get('cart_' . $sessionId, []);

        // Thêm sản phẩm vào giỏ hàng
        $cart[] = [
            'product_id' => $validated['product_id'],
            'product_variant_id' => $validated['product_variant_id'],
            'quantity' => $validated['quantity'],
            'price' => $validated['price'],
        ];

        // Lưu giỏ hàng vào session với session ID cụ thể
        $request->session()->put('cart_' . $sessionId, $cart);

        return response()->json([
            'message' => 'Product added to cart successfully',
            'cart' => $cart,
            'session_id' => $sessionId, // Trả về session ID để kiểm tra
        ]);
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
