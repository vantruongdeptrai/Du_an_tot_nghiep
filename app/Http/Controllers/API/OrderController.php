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

class OrderController extends Controller
{
    //  Thanh toán cho người dùng  đăng nhập 

    public function PaymentLogin(Request $request)
    {
        $user = User::find($request->input('user_id'));

        if (!$user) {
            return response()->json(['message' => 'Người dùng không xác thực'], 401);
        }

        DB::beginTransaction();
        try {
            // Tính tổng giá trị đơn hàng
            $totalPrice = 0;
            $orderItems = $request->input('order_items'); 

            foreach ($orderItems as $item) {
                $productVariant = ProductVariant::find($item['product_variant_id']);
                $totalPrice += $productVariant->price * $item['quantity'];

                // Giảm số lượng sản phẩm tương ứng
                $productVariant->quantity -= $item['quantity'];
                $productVariant->save();
            }

            // Áp dụng mã giảm giá nếu có
            if ($request->input('coupon_id')) {
                $coupon = Coupon::find($request->input('coupon_id'));
                if ($totalPrice >= $coupon->min_order_value) {
                    $totalPrice -= $coupon->discount_amount;
                }
            }

            // Lưu thông tin đơn hàng
            $order = new Order();
            $order->user_id = $user->id;
            $order->total_price = $totalPrice;
            $order->status_order = 'chờ xử lý'; 
            $order->payment_type = $request->input('payment_type');
            $order->shipping_address = $request->input('shipping_address');
            $order->coupon_id = $request->input('coupon_id'); 
            $order->phone_order = $request->input('phone_order'); 
            $order->name_order = $request->input('name_order'); 
            $order->email_order = $request->input('email_order'); 
            $order->user_note = $request->input('user_note'); 
            $order->save();

            // Lưu thông tin chi tiết đơn hàng (order_items)
            foreach ($orderItems as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_variant_id = $item['product_variant_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->save();
            }

            DB::commit();

            // Trả về phản hồi sau khi thanh toán thành công
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