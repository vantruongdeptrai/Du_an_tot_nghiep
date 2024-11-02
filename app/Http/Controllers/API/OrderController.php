<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Product;


class OrderController extends Controller
{
    //  Thanh toán cho người dùng  đăng nhập 

    public function PaymentLogin(Request $request)
{
    $user = User::find($request->input('user_id'));
    if (!$user) {
        return response()->json(['message' => 'không có người dùng'], 401);
    }

    DB::beginTransaction();
    try {
        // Khởi tạo tổng giá sản phẩm thường và biến thể
        $totalPriceProduct = 0;
        $totalPriceVariant = 0;
        $orderItems = $request->input('order_items'); 

        $order = new Order();
        $order->user_id = $user->id;
        $order->status_order = 'Chờ xác nhận'; 
        $order->payment_type = $request->input('payment_type'); 
        $order->shipping_address = $request->input('shipping_address');
        $order->coupon_id = $request->input('coupon_id'); 
        $order->phone_order = $request->input('phone_order'); 
        $order->name_order = $request->input('name_order'); 
        $order->email_order = $request->input('email_order'); 
        $order->user_note = $request->input('user_note'); 
        $order->save();

        // Lưu chi tiết sản phẩm bình thường và biến thể
        foreach ($orderItems as $item) {
            if (array_key_exists('product_id', $item)) {
                // Xử lý sản phẩm thường
                $product = Product::find($item['product_id']);

                if (!$product) {
                    return response()->json(['message' => 'Không tìm thấy sản phẩm: ' . $item['product_id']], 404);
                }

                // Kiểm tra số lượng
                if ($product->quantity < $item['quantity']) {
                    return response()->json(['message' => 'Số lượng sản phẩm không đủ: ' . $item['product_id']], 400);
                }

                // Xử lý sản phẩm khi còn sale 
                $price = $product->sale_price && $product->sale_start <= now() && $product->sale_end >= now() 
                    ? $product->sale_price 
                    : $product->price;

                // Tổng giá trị sản phẩm
                $totalPriceProduct += $price * $item['quantity'];

                // Lưu chi tiết đơn hàng sản phẩm
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id; 
                $orderItem->product_id = $item['product_id']; 
                $orderItem->product_variant_id = null; 
                $orderItem->quantity = $item['quantity'];
                $orderItem->save();

                // Giảm số lượng sản phẩm
                $product->quantity -= $item['quantity'];
                $product->save();
            }
            else if (array_key_exists('product_variant_id', $item)) {
                // Xử lý sản phẩm biến thể
                $productVariant = ProductVariant::find($item['product_variant_id']);

                if (!$productVariant) {
                    return response()->json(['message' => 'Không có sản phẩm biến thể ' . $item['product_variant_id']], 404);
                }

                // Kiểm tra số lượng biến thể
                if ($productVariant->quantity < $item['quantity']) {
                    return response()->json(['message' => 'Số lượng sản phẩm biến thể không đủ: ' . $item['product_variant_id']], 400);
                }

                $price = $productVariant->sale_price && $productVariant->sale_start <= now() && $productVariant->sale_end >= now() 
                    ? $productVariant->sale_price 
                    : $productVariant->price;

                // Tính tổng
                $totalPriceVariant += $price * $item['quantity'];

                // Giảm số lượng sản phẩm biến thể
                $productVariant->quantity -= $item['quantity'];
                $productVariant->save();

                // Chi tiết sản phẩm biến thể
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id; 
                $orderItem->product_id = $productVariant->product_id; 
                $orderItem->product_variant_id = $item['product_variant_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->save();
            } else {
                return response()->json(['message' => 'Không xác định được sản phẩm'], 400);
            }
        }

        $totalPrice = $totalPriceProduct + $totalPriceVariant;

        if ($request->input('coupon_id')) {
            $coupon = Coupon::find($request->input('coupon_id'));
            if ($coupon && $totalPrice >= $coupon->min_order_value) {
                $totalPrice -= $coupon->discount_amount;
            }
        }

        $order->total_price = $totalPrice; 
        $order->save(); 

        DB::commit();

        return response()->json([
            'message' => 'Thanh toán thành công!',
            'order_id' => $order->id,
            'total_price' => $totalPrice,
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Lỗi khi xử lý thanh toán: ' . $e->getMessage()], 500);
    }
}

    

        //  Thanh toán cho người dùng   ko  đăng nhập 

        public function PaymentNoLogin(Request $request)
        {
            $sessionId = $request->header('session_id');
            $cart = $request->session()->get('cart_' . $sessionId, []);
            
            if (empty($cart)) {
                return response()->json(['message' => 'Không có sản phẩm trong giỏ hàng'], 400);
            }
        
            DB::beginTransaction();
            try {
                $totalPriceProduct = 0;
                $totalPriceVariant = 0;
        
                // Khởi tạo đơn hàng
                $order = new Order();
                $order->user_id = null;  // Không có user_id vì là người dùng khách
                $order->status_order = 'Chờ xác nhận';
                $order->payment_type = $request->input('payment_type'); 
                $order->shipping_address = $request->input('shipping_address');
                $order->coupon_id = $request->input('coupon_id'); 
                $order->phone_order = $request->input('phone_order'); 
                $order->name_order = $request->input('name_order'); 
                $order->email_order = $request->input('email_order'); 
                $order->user_note = $request->input('user_note'); 
                $order->save();
        
                // Lưu chi tiết sản phẩm trong giỏ hàng
                foreach ($cart as $item) {
                    if (isset($item['product']['id'])) {
                        // Sản phẩm thông thường
                        $product = Product::find($item['product']['id']);
        
                        if (!$product) {
                            return response()->json(['message' => 'Không tìm thấy sản phẩm: ' . $item['product']['id']], 404);
                        }
        
                        // Kiểm tra số lượng sản phẩm
                        if ($product->quantity < $item['quantity']) {
                            return response()->json(['message' => 'Số lượng không đủ cho sản phẩm: ' . $item['product']['id']], 400);
                        }
        
                        $price = ($product->sale_price && $product->sale_start <= now() && $product->sale_end >= now()) 
                            ? $product->sale_price 
                            : $product->price;
        
                        $totalPriceProduct += $price * $item['quantity'];
        
                        // Lưu chi tiết đơn hàng cho sản phẩm thông thường
                        $orderItem = new OrderItem();
                        $orderItem->order_id = $order->id; 
                        $orderItem->product_id = $item['product']['id']; 
                        $orderItem->product_variant_id = null; 
                        $orderItem->quantity = $item['quantity'];
                        $orderItem->save();
        
                        // Giảm số lượng sản phẩm
                        $product->quantity -= $item['quantity'];
                        $product->save();
                    } elseif (isset($item['product_variant']['id'])) {
                        // Sản phẩm biến thể
                        $productVariant = ProductVariant::find($item['product_variant']['id']);
        
                        if (!$productVariant) {
                            return response()->json(['message' => 'Không tìm thấy sản phẩm biến thể ' . $item['product_variant']['id']], 404);
                        }
        
                        // Kiểm tra số lượng biến thể
                        if ($productVariant->quantity < $item['quantity']) {
                            return response()->json(['message' => 'Số lượng không đủ cho sản phẩm biến thể ' . $item['product_variant']['id']], 400);
                        }
        
                        $price = ($productVariant->sale_price && $productVariant->sale_start <= now() && $productVariant->sale_end >= now()) 
                            ? $productVariant->sale_price 
                            : $productVariant->price;
        
                        $totalPriceVariant += $price * $item['quantity'];
        
                        // Giảm số lượng sản phẩm biến thể
                        $productVariant->quantity -= $item['quantity'];
                        $productVariant->save();
        
                        // Lưu chi tiết đơn hàng cho sản phẩm biến thể
                        $orderItem = new OrderItem();
                        $orderItem->order_id = $order->id; 
                        $orderItem->product_id = $productVariant->product_id; 
                        $orderItem->product_variant_id = $item['product_variant']['id'];
                        $orderItem->quantity = $item['quantity'];
                        $orderItem->save();
                    } else {
                        return response()->json(['message' => 'Không xác định được sản phẩm'], 400);
                    }
                }
        
                $totalPrice = $totalPriceProduct + $totalPriceVariant;
        
                // Áp dụng mã giảm giá
                if ($request->input('coupon_id')) {
                    $coupon = Coupon::find($request->input('coupon_id'));
                    if ($coupon && $totalPrice >= $coupon->min_order_value) {
                        $totalPrice -= $coupon->discount_amount;
                    }
                }
        
                $order->total_price = $totalPrice; 
                $order->save();
        
                DB::commit();
        
                // Xóa giỏ hàng trong session sau khi thanh toán
                $request->session()->forget('cart_' . $sessionId);
        
                return response()->json([
                    'message' => 'Thanh toán thành công!',
                    'order_id' => $order->id,
                    'total_price' => $totalPrice,
                ], 201);
        
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Lỗi khi xử lý thanh toán: ' . $e->getMessage()], 500);
            }
        }
        
}