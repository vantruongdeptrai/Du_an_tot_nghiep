<?php

namespace App\Http\Controllers\API;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
public function getCartForAuth()
{
    $userId = Auth::id(); // Lấy ID người dùng đã đăng nhập
    $cartItems = Cart::where('user_id', $userId)->get();

    return response()->json($cartItems);
}

public function getCartForGuest()
{
    // Lấy giỏ hàng từ session
    $cart = session()->get('cart', []);

    return response()->json($cart);
}
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
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
