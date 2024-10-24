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

class PaymentController extends Controller
{
    /**
     * Thanh toán cho người dùng đã đăng nhập
     */
    public function PaymentLogin(Request $request)
    {
        // Giả sử người dùng đã được xác thực
        $user = User::find($request->input('user_id'));

        if (!$user) {
            return response()->json(['message' => 'Người dùng không xác thực'], 401);
        }

        DB::beginTransaction();
        try {
            // Tính tổng giá trị đơn hàng
            $totalPrice = 0;
            $orderItems = $request->input('order_items'); // Mảng gồm product_variant_id và quantity

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
            $order->status_order = 'pending'; // Trạng thái chờ xác nhận
            $order->payment_type = $request->input('payment_type'); // Loại thanh toán
            $order->shipping_address = $request->input('shipping_address');
            $order->coupon_id = $request->input('coupon_id'); // Mã giảm giá (nếu có)
            $order->phone_order = $request->input('phone_order'); // Số điện thoại
            $order->name_order = $request->input('name_order'); // Tên người nhận
            $order->email_order = $request->input('email_order'); // Email người nhận
            $order->user_note = $request->input('user_note'); // Ghi chú của khách hàng
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

    /**
     * Thanh toán cho người dùng không đăng nhập (khách)
     */
    public function PaymentNoLogin(Request $request)
    {
        // Giả sử sử dụng session ID để theo dõi người dùng khách
        $sessionId = $request->session()->getId();
        $cart = $request->session()->get('cart_' . $sessionId, []);

        if (empty($cart)) {
            return response()->json(['message' => 'Giỏ hàng rỗng'], 400);
        }

        DB::beginTransaction();
        try {
            // Tính tổng giá trị đơn hàng cho khách
            $totalPrice = 0;
            foreach ($cart as $item) {
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

            // Tạo đơn hàng cho khách (không có user_id)
            $order = new Order();
            $order->user_id = null; // Người dùng không đăng nhập
            $order->total_price = $totalPrice;
            $order->status_order = 'pending'; // Trạng thái chờ xác nhận
            $order->payment_type = $request->input('payment_type'); // Loại thanh toán
            $order->shipping_address = $request->input('shipping_address');
            $order->coupon_id = $request->input('coupon_id'); // Mã giảm giá (nếu có)
            $order->phone_order = $request->input('phone_order'); // Số điện thoại
            $order->name_order = $request->input('name_order'); // Tên người nhận
            $order->email_order = $request->input('email_order'); // Email người nhận
            $order->user_note = $request->input('user_note'); // Ghi chú của khách hàng
            $order->save();

            // Lưu chi tiết đơn hàng
            foreach ($cart as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_variant_id = $item['product_variant_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->save();
            }

            DB::commit();

            // Xóa giỏ hàng của khách sau khi thanh toán thành công
            $request->session()->forget('cart_' . $sessionId);

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

