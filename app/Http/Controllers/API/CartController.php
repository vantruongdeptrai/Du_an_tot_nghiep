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
    // Xác thực dữ liệu đầu vào
    $validated = $request->validate([
        'product_id' => 'nullable|exists:products,id',
        'product_variant_id' => 'nullable|exists:product_variants,id',
        'quantity' => 'required|integer|min:1',
    ]);

    // Lấy session ID để xác định khách hàng
    $sessionId = $request->session()->getId();

    // Lấy giỏ hàng từ session
    $cart = $request->session()->get('cart_' . $sessionId, []);

    // Kiểm tra nếu sản phẩm đã tồn tại trong giỏ hàng
    $exists = false;
    foreach ($cart as &$item) {
        if (
            $item['product_id'] == $request->product_id &&
            $item['product_variant_id'] == $request->product_variant_id
        ) {
            // Cập nhật số lượng nếu sản phẩm đã tồn tại
            $item['quantity'] += $request->quantity;
            $exists = true;
            break;
        }
    }

    // Nếu sản phẩm chưa tồn tại, thêm mới vào giỏ hàng
    if (!$exists) {
        $cart[] = [
            'id' => md5(($request->product_id ?? '0') . '-' . ($request->product_variant_id ?? '0') . '-' . time()), // ID duy nhất
            'product_id' => $request->product_id,
            'product_variant_id' => $request->product_variant_id,
            'quantity' => $request->quantity,
            'product' => Product::find($request->product_id), // Thêm thông tin sản phẩm
            'product_variant' => $request->product_variant_id
                ? ProductVariant::find($request->product_variant_id)
                : null, // Thêm thông tin biến thể nếu có
        ];
    }

    // Lưu giỏ hàng vào session
    $request->session()->put('cart_' . $sessionId, $cart);

    // Trả về phản hồi JSON
    return response()->json([
        'message' => $exists
            ? 'Product quantity updated in cart successfully'
            : 'Product added to cart successfully',
        'cart' => $cart,
        'session_id' => $sessionId,
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
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $cart->product_variant_id,
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







public function getCart(Request $request)
{
    $sessionId = $request->header('session_id');
    $cart = $request->session()->get('cart_' . $sessionId, []);

    $filteredCart = array_filter($cart, function ($item) {
        return $item['quantity'] > 1; // Chỉ giữ lại những sản phẩm có quantity > 1
    });

    $now = now();

    $detailedCart = array_map(function ($item) use ($now) {
        $cartId = $item['id'] ?? null; // Lấy cart_id từ session (nếu có)
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
                'cart_id' => $cartId,
                'product_id' => $productId,
                'product_variant_id' => $productVariantId,
                'product_name' => $product->name ?? 'N/A',
                'quantity' => $item['quantity'],
                'price' => $price,
                'sale_price' => $salePrice,
                'sale_start' => $saleStart,
                'sale_end' => $saleEnd,
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
                'cart_id' => $cartId,
                'product_id' => $productId,
                'product_variant_id' => $productVariantId,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'price' => $price,
                'sale_price' => $salePrice,
                'sale_start' => $saleStart,
                'sale_end' => $saleEnd,
                'size' => 'N/A',
                'color' => 'N/A',
                'product_image' => isset($product->image)
                    ? asset('storage/' . $product->image)
                    : 'N/A',
            ];
        }

        return [
            'cart_id' => $cartId,
            'product_id' => 'N/A',
            'product_variant_id' => 'N/A',
            'product_name' => 'N/A',
            'quantity' => $item['quantity'],
            'price' => 'N/A',
            'sale_price' => 'N/A',
            'sale_start' => 'N/A',
            'sale_end' => 'N/A',
            'size' => 'N/A',
            'color' => 'N/A',
            'product_image' => 'N/A',
        ];
    }, $filteredCart);

    return response()->json(['cart' => $detailedCart]);
}

public function updateCart(Request $request)
{
    $request->validate([
        'cart_id' => 'required|exists:carts,id',
        'quantity' => 'required|integer|min:1',
        'size_name' => 'nullable|string|max:255',
        'color_name' => 'nullable|string|max:255',
    ]);

    // Tìm giỏ hàng theo cart_id
    $cart = Cart::find($request->input('cart_id'));

    if ($cart) {
        // Cập nhật số lượng
        $cart->quantity = $request->input('quantity');

        // Kiểm tra và cập nhật biến thể sản phẩm
        if ($request->has('product_variant_id')) {
            $productVariantId = $request->input('product_variant_id');
            if (ProductVariant::find($productVariantId)) {
                $cart->product_variant_id = $productVariantId;
            } else {
                return response()->json(['message' => 'Biến thể sản phẩm không hợp lệ'], 400);
            }
        }

        // Lưu thay đổi giỏ hàng
        $cart->save();

        // Cập nhật tên size
        if ($request->has('size_name') && $cart->productVariant && $cart->productVariant->size) {
            $cart->productVariant->size->name = $request->input('size_name');
            $cart->productVariant->size->save();
        }

        // Cập nhật tên màu
        if ($request->has('color_name') && $cart->productVariant && $cart->productVariant->color) {
            $cart->productVariant->color->name = $request->input('color_name');
            $cart->productVariant->color->save();
        }

        // Lấy thông tin giỏ hàng cập nhật
        $updatedCart = Cart::where('id', $cart->id)
            ->with([
                'productVariant',
                'productVariant.product' => function ($query) {
                    $query->select('id', 'name', 'image', 'price', 'sale_price', 'sale_start', 'sale_end');
                },
                'productVariant.size' => function ($query) {
                    $query->select('id', 'name');
                },
                'productVariant.color' => function ($query) {
                    $query->select('id', 'name');
                },
            ])
            ->first();

        // Trả về giỏ hàng cập nhật
        return response()->json([
            'message' => 'Cập nhật giỏ hàng thành công',
            'cart' => [
                'id' => $updatedCart->id,
                'product_id' => $updatedCart->productVariant->product->id ?? null,
                'product_variant_id' => $updatedCart->product_variant_id,
                'quantity' => $updatedCart->quantity,
                'price' => $updatedCart->productVariant->sale_price
                    ?? $updatedCart->productVariant->price,
                'size' => $updatedCart->productVariant->size->name ?? 'N/A',
                'color' => $updatedCart->productVariant->color->name ?? 'N/A',
                'product_name' => $updatedCart->productVariant->product->name ?? 'N/A',
                'product_image' => $updatedCart->productVariant->product->image
                    ? asset('storage/' . $updatedCart->productVariant->product->image)
                    : null,
            ],
        ]);
    }

    return response()->json(['message' => 'Giỏ hàng không tồn tại'], 404);
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
        $cart = Cart::find($id);
        if ($cart) {
            $cart->delete(); // Xóa mềm, không xóa hẳn trong DB
            return response()->json(['message' => 'Cart deleted successfully'], 200);
        }
    
        return response()->json(['message' => 'Cart not found'], 404);
    }
    public function removeFromCartGuest(Request $request, $id)
{
    // Xác thực dữ liệu đầu vào (không cần nữa vì id đã có trong URL)
    // Lấy session ID để xác định khách hàng
    $sessionId = $request->session()->getId();

    // Lấy giỏ hàng từ session
    $cart = $request->session()->get('cart_' . $sessionId, []);

    // Tìm và xóa sản phẩm khỏi giỏ hàng dựa trên id
    $cart = array_filter($cart, function ($item) use ($id) {
        // Xóa sản phẩm nếu id trùng với id được gửi
        return $item['id'] !== $id;
    });

    // Làm lại chỉ số mảng sau khi xóa
    $cart = array_values($cart);

    // Lưu giỏ hàng đã cập nhật vào session
    $request->session()->put('cart_' . $sessionId, $cart);

    // Trả về phản hồi JSON
    return response()->json([
        'message' => 'Product removed from cart successfully',
        'cart' => $cart,
        'session_id' => $sessionId,
    ]);
}

}