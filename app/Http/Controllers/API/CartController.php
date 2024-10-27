<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Storage;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;
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
            // 'price' => 'required|numeric',
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
                // 'price' => $request->price,
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
            // 'price' => 'required|numeric',
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
            //   
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
    $user = User::find($userId);

    if ($user) {
        $carts = Cart::where('user_id', $user->id)
                    ->with([
                        'productVariant',
                        'productVariant.product' => function($query) {
                            $query->select('id', 'name', 'image', 'price', 'sale_price', 'sale_start', 'sale_end');
                        },
                        'productVariant.size' => function($query) {
                            $query->select('id', 'name');
                        },
                        'productVariant.color' => function($query) {
                            $query->select('id', 'name');
                        },
                    ])
                    ->get();

        return response()->json([
            'cart' => $carts->map(function($cart) {
                $product = $cart->productVariant ? $cart->productVariant->product : Product::find($cart->product_id);
                $productName = $product->name;
                $productImage = $product->image ? asset('storage/' . $product->image) : null;

                // Kiểm tra thời gian hiện tại với thời gian sale từ biến thể hoặc từ sản phẩm gốc
                $now = now();
                if ($cart->productVariant) {
                    // Nếu có biến thể, dùng thời gian sale và giá từ biến thể
                    $saleStart = $cart->productVariant->sale_start;
                    $saleEnd = $cart->productVariant->sale_end;
                    $price = ($now >= $saleStart && $now <= $saleEnd && $cart->productVariant->sale_price) 
                        ? $cart->productVariant->sale_price 
                        : $cart->productVariant->price;
                } else {
                    // Nếu không có biến thể, dùng thời gian sale và giá từ product
                    $saleStart = $product->sale_start;
                    $saleEnd = $product->sale_end;
                    $price = ($now >= $saleStart && $now <= $saleEnd && $product->sale_price) 
                        ? $product->sale_price 
                        : $product->price;
                }

                return [
                    'product_name' => $productName,
                    'quantity' => $cart->quantity,
                    'price' => $price,
                    'size' => $cart->productVariant && $cart->productVariant->size 
                        ? $cart->productVariant->size->name 
                        : 'N/A',
                    'color' => $cart->productVariant && $cart->productVariant->color 
                        ? $cart->productVariant->color->name 
                        : 'N/A',
                    'product_image' => $productImage,
                ];
            }),
        ]);
    }

    return response()->json(['message' => 'Người dùng chưa được xác thực'], 401);
}





// public function getCart(Request $request)
// {
//     $sessionId = $request->header('session_id');
//     $cart = $request->session()->get('cart_' . $sessionId, []);

//     // Thời gian hiện tại
//     $now = now();

//     // Cập nhật giỏ hàng
//     foreach ($cart as &$item) {
//         if (isset($item['product_variant'])) {
//             $saleStart = $item['product_variant']['sale_start'];
//             $saleEnd = $item['product_variant']['sale_end'];
//             $salePrice = $item['product_variant']['sale_price'];
//             $regularPrice = $item['product_variant']['price'];

//             // Kiểm tra nếu giá sale đã kết thúc
//             if ($saleStart && $saleEnd && !$now->between($saleStart, $saleEnd)) {
//                 $item['product_variant']['price'] = $regularPrice; // Đặt lại giá về giá gốc
//                 unset($item['product_variant']['sale_price']); // Xóa giá sale
//             }
//         } elseif (isset($item['product'])) {
//             $saleStart = $item['product']['sale_start'];
//             $saleEnd = $item['product']['sale_end'];
//             $salePrice = $item['product']['sale_price'];
//             $regularPrice = $item['product']['price'];

//             // Kiểm tra nếu giá sale đã kết thúc
//             if ($saleStart && $saleEnd && !$now->between($saleStart, $saleEnd)) {
//                 $item['product']['price'] = $regularPrice; // Đặt lại giá về giá gốc
//                 unset($item['product']['sale_price']); // Xóa giá sale
//             }
//         }
//     }

//     $detailedCart = array_map(function($item) use ($now) {
//         $price = 0;
//         $size = 'N/A';
//         $color = 'N/A';
//         $productImage = 'N/A';

//         if (isset($item['product_variant'])) {
//             $saleStart = $item['product_variant']['sale_start'];
//             $saleEnd = $item['product_variant']['sale_end'];
//             $salePrice = $item['product_variant']['sale_price'];
//             $regularPrice = $item['product_variant']['price'];

//             // Kiểm tra giá cho biến thể
//             if ($saleStart && $saleEnd && $now->between($saleStart, $saleEnd) && $salePrice) {
//                 $price = $salePrice;
//             } else {
//                 $price = $regularPrice;
//             }

//             $size = $item['product_variant']['size']['name'];
//             $color = $item['product_variant']['color']['name'];
//             $productImage = asset('storage/' . $item['product_variant']['image']);
//         } elseif (isset($item['product'])) {
//             $saleStart = $item['product']['sale_start'];
//             $saleEnd = $item['product']['sale_end'];
//             $salePrice = $item['product']['sale_price'];
//             $regularPrice = $item['product']['price'];

//             // Kiểm tra giá cho sản phẩm
//             if ($saleStart && $saleEnd && $now->between($saleStart, $saleEnd) && $salePrice) {
//                 $price = $salePrice;
//             } else {
//                 $price = $regularPrice;
//             }

//             $productImage = asset('storage/' . $item['product']['image']);
//         }

//         return [
//             'product_name' => $item['product']['name'] ?? 'N/A',
//             'quantity' => $item['quantity'],
//             'price' => $price,
//             'size' => $size,
//             'color' => $color,
//             'product_image' => $productImage,
//         ];
//     }, $cart);

//     // Cập nhật lại giỏ hàng trong session
//     $request->session()->put('cart_' . $sessionId, $cart);

//     return response()->json(['cart' => $detailedCart]);
//     $request->session()->forget('cart_' . $sessionId);
// }

public function getCart(Request $request)
{
    $sessionId = $request->header('session_id');
    $cart = $request->session()->get('cart_' . $sessionId, []);

    $filteredCart = array_filter($cart, function($item) {
        return $item['quantity'] > 1; // Chỉ giữ lại những sản phẩm có quantity > 1
    });

    $now = now();

    $detailedCart = array_map(function($item) use ($now) {
        $productId = $item['product']['id'] ?? null;
        $productVariantId = $item['product_variant']['id'] ?? null;

        // Truy vấn product từ cơ sở dữ liệu dựa trên product_id
        $product = Product::find($productId);

        // Truy vấn product_variant từ cơ sở dữ liệu dựa trên product_variant_id nếu có
        $productVariant = $productVariantId ? ProductVariant::find($productVariantId) : null;

        if ($productVariant) {
            // Lấy thông tin giá và sale từ biến thể
            $saleStart = $productVariant->sale_start;
            $saleEnd = $productVariant->sale_end;
            $salePrice = $productVariant->sale_price;
            $price = ($saleStart && $saleEnd && $now->between($saleStart, $saleEnd) && $salePrice)
                ? $salePrice
                : $productVariant->price;

            return [
                'product_name' => $product->name ?? 'N/A',
                'quantity' => $item['quantity'],
                'price' => $price,
                'size' => $productVariant->size->name ?? 'N/A',
                'color' => $productVariant->color->name ?? 'N/A',
                'product_image' => isset($product->image) 
                    ? asset('storage/' . $product->image)
                    : 'N/A',
            ];
        } elseif ($product) {
            // Lấy thông tin giá và sale từ sản phẩm đơn thể
            $saleStart = $product->sale_start;
            $saleEnd = $product->sale_end;
            $salePrice = $product->sale_price;
            $price = ($saleStart && $saleEnd && $now->between($saleStart, $saleEnd) && $salePrice)
                ? $salePrice
                : $product->price;

            return [
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'price' => $price,
                'size' => 'N/A',
                'color' => 'N/A',
                'product_image' => isset($product->image) 
                    ? asset('storage/' . $product->image)
                    : 'N/A',
            ];
        }

        return [
            'product_name' => 'N/A',
            'quantity' => $item['quantity'],
            'price' => 'N/A',
            'size' => 'N/A',
            'color' => 'N/A',
            'product_image' => 'N/A',
        ];
    }, $filteredCart);

    return response()->json(['cart' => $detailedCart]);
  
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
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
