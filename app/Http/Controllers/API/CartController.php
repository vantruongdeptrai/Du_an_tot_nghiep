<?php

namespace App\Http\Controllers\API;


use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;



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

    public function showCart()
    {
        // Người dùng đã đăng nhập, lấy giỏ hàng của họ
        $carts = Cart::where('user_id', auth()->id())
            ->with('product', 'productVariant')
            ->get();

        // Tính tổng tiền giỏ hàng
        $totalPrice = $carts->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        // Trả về giỏ hàng và tổng tiền
        return response()->json([
            'cart' => $carts,
            'total_price' => $totalPrice
        ]);
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
