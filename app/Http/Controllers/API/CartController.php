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

//Hiển thị giỏ hàng cho người chưa đăng nhập
public function getCart(Request $request)
{
    // Lấy session ID từ header
    $sessionId = $request->header('Session-ID');

    // Lấy giỏ hàng dựa trên session ID, nếu không có thì trả về mảng rỗng
    $cart = $request->session()->get('cart_' . $sessionId, []);

    // Lọc sản phẩm có số lượng > 1
    $filteredCart = array_filter($cart, function($item) {
        return $item['quantity'] > 1;
    });

    // Tính tổng tiền giỏ hàng
    $totalPrice = array_sum(array_map(function($item) {
        return $item['quantity'] * $item['price'];
    }, $filteredCart));

    // Trả về dữ liệu giỏ hàng cùng với tổng tiền
    return response()->json([
        'cart' => $filteredCart,
        'total_price' => $totalPrice
    ]);
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
public function getCartUser(Request $request)
{
    // Lấy người dùng đã đăng nhập
    $user = $request->user();
    // Nếu người dùng đã đăng nhập, lấy giỏ hàng từ model Cart dựa trên user_id
    if ($user) {
        $carts = Cart::where('user_id', $user->id)
                    ->with('productVariant')
                    ->where('quantity', '>', 1)

                    ->get();
        //Tính tổng tiền giỏ hàng
        $totalPrice = $carts->sum(function($item) {
        return $item->quantity * $item->price;
    });
        return response()->json([
            'cart' => $carts,
          'total_price' => $totalPrice
        ]);
    }

    return response()->json(['message' => 'Người dùng chưa được xác thực'], 401);
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
