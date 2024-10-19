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
      // Xác thực dữ liệu đầu vào
      $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
        'product_variant_id' => 'nullable|exists:product_variants,id',
        'quantity' => 'required|integer|min:1',
        'price' => 'required|numeric',
    ]);

    // Kiểm tra guest token
    $guestToken = $request->header('Authorization') ?? 'Bearer ' . Str::random(60);

    // Lưu sản phẩm vào giỏ hàng cho người dùng chưa đăng nhập
    Cart::updateOrCreate(
        [
            'guest_token' => $guestToken,
            'product_id' => $request->product_id,
            'product_variant_id' => $request->product_variant_id,
        ],
        [
            'quantity' => DB::raw('quantity + ' . $request->quantity),
            'price' => $request->price,
        ]
    );

    return response()->json([
        'guest_token' => $guestToken,
        'message' => 'Product added to cart successfully for guest'
    ]);

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
