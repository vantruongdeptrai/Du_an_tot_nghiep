<?php

namespace App\Http\Controllers\API;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function addToCart(Request $request)
    {
        // Logic cho người dùng đã đăng nhập
        $request->validate([
            'user_id'=> 'required|exists:users,id',
            'product_id' => 'nullable|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);

        // $userId = Auth::id(); // Lấy ID người dùng nếu đã đăng nhập
        Cart::updateOrCreate(
            [
                'user_id' => $request->user_id,
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
// Hiển thị giỏ hàng cho người chưa đăng nhập



    public function addToCartGuest(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
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
            'product' => Product::find($request->product_id),
            'product_variant' => $request->product_variant_id ? ProductVariant::find($request->product_variant_id) : null,
            'quantity' => $request->quantity,
            'price' => $request->price,
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
    $userId = $request->input('user_id');
    // Lấy người dùng từ ID
    $user = User::find($userId);

    // Nếu người dùng đã đăng nhập, lấy giỏ hàng từ model Cart dựa trên user_id
    if ($user) {
        $carts = Cart::where('user_id', $user->id)
                    ->with([
                        'productVariant',
                        'productVariant.product' => function($query) {
                            $query->select('id', 'name', 'image'); // Lấy tên và ảnh sản phẩm từ bảng products
                        },
                        'productVariant.size' => function($query) {
                            $query->select('id', 'name'); // Lấy size từ bảng sizes
                        },
                        'productVariant.color' => function($query) {
                            $query->select('id', 'name'); // Lấy màu từ bảng colors
                        },
                    ])
                    ->get();

        // Tính tổng tiền giỏ hàng
        $totalPrice = $carts->sum(function($item) {
            return $item->quantity * $item->price;
        });

        // Trả về phản hồi JSON với các thông tin chi tiết hơn
        return response()->json([
            'cart' => $carts->map(function($cart) {
                $productName = $cart->productVariant 
                    ? $cart->productVariant->product->name 
                    : Product::find($cart->product_id)->name; // Tên sản phẩm từ productVariant

                // Sử dụng asset() để lấy URL của ảnh sản phẩm
                $productImage = $cart->productVariant 
                    ? ($cart->productVariant->product->image ? asset('storage/' . $cart->productVariant->product->image) : null)
                    : (Product::find($cart->product_id)->image ? asset('storage/' . Product::find($cart->product_id)->image) : null);

                return [
                    'product_name' => $productName,
                    'quantity' => $cart->quantity,
                    'price' => $cart->price,
                    'size' => $cart->productVariant && $cart->productVariant->size 
                        ? $cart->productVariant->size->name 
                        : 'N/A', // Nếu có size thì lấy, nếu không thì trả về N/A
                    'color' => $cart->productVariant && $cart->productVariant->color 
                        ? $cart->productVariant->color->name 
                        : 'N/A', // Nếu có màu thì lấy, nếu không thì trả về N/A
                    'product_image' => $productImage, // URL của ảnh từ storage
                ];
            }),
            'total_price' => $totalPrice
        ]);
    }

    return response()->json(['message' => 'Người dùng chưa được xác thực'], 401);
}

public function getCart(Request $request)
{
    // Lấy session ID từ header
    $sessionId = $request->header('session_id');

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

    // Chuẩn bị dữ liệu chi tiết sản phẩm cho từng sản phẩm trong giỏ hàng
    $detailedCart = array_map(function($item) {
        // Xử lý trường hợp sản phẩm có biến thể
        if (isset($item['product_variant'])) {
            return [
                'product_name' => $item['product_variant']['product']['name'] ?? 'N/A', // Tên sản phẩm
                'quantity' => $item['quantity'], // Số lượng
                'price' => $item['price'], // Giá
                'size' => $item['product_variant']['size']['name'] ?? 'N/A', // Size của sản phẩm
                'color' => $item['product_variant']['color']['name'] ?? 'N/A', // Màu của sản phẩm
                'product_image' => $item['product_variant']['product']['image'] ?? 'N/A', // Ảnh của sản phẩm
            ];
        } 
        // Xử lý trường hợp sản phẩm không có biến thể
        elseif (isset($item['product'])) {
            return [
                'product_name' => $item['product']['name'] ?? 'N/A', // Tên sản phẩm
                'quantity' => $item['quantity'], // Số lượng
                'price' => $item['price'], // Giá
                'size' => 'N/A', // Không có size
                'color' => 'N/A', // Không có màu
                'product_image' => $item['product']['image'] ?? 'N/A', // Ảnh của sản phẩm
            ];
        }

        // Nếu không có cả product_variant và product, trả về thông tin mặc định
        return [
            'product_name' => 'N/A',
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'size' => 'N/A',
            'color' => 'N/A',
            'product_image' => 'N/A',
        ];
    }, $filteredCart);

    // Trả về dữ liệu giỏ hàng cùng với tổng tiền
    return response()->json([
        'cart' => $detailedCart,
        'total_price' => $totalPrice
    ]);
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
