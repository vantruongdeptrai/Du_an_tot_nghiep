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
use App\Models\Cart;
use App\Jobs\SendOrderSuccessMail;



class OrderController extends Controller
{
    //  Thanh toán cho người dùng  đăng nhập 

    public function PaymentLogin(Request $request)
    {
        $user = User::find($request->input('user_id'));
        if (!$user) {
            return response()->json(['message' => 'Không có người dùng'], 401);
        }
    
        DB::beginTransaction();
        try {
            // Khởi tạo tổng giá sản phẩm thường và biến thể
            $totalPriceProduct = 0;
            $totalPriceVariant = 0;
            $orderItems = $request->input('order_items');
    
            // Tạo đơn hàng
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
                } else if (array_key_exists('product_variant_id', $item)) {
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
    
                    // Tổng giá trị sản phẩm biến thể
                    $totalPriceVariant += $price * $item['quantity'];
    
                    // Giảm số lượng sản phẩm biến thể
                    $productVariant->quantity -= $item['quantity'];
                    $productVariant->save(); 
    
                    // Lưu chi tiết sản phẩm biến thể
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
    
               // Mã giảm giá - Tìm coupon theo name
               if ($request->input('coupon_name')) {
                // Lấy mã giảm giá từ tên
                $coupon = Coupon::where('name', $request->input('coupon_name'))->first();
                
                if ($coupon) {
                    // Kiểm tra xem mã giảm giá có còn sử dụng được không (check thời gian, active, và usage limit)
                    if ($coupon->is_active 
                        && $totalPrice >= $coupon->min_order_value 
                        && $coupon->start_date <= now() 
                        && $coupon->end_date >= now()) {
                        
                        $discount = $totalPrice * ($coupon->discount_amount / 100);
                        
                        // Nếu giá trị giảm giá lớn hơn tổng đơn hàng, giảm giá không được vượt quá tổng giá trị đơn hàng
                        if ($discount > $totalPrice) {
                            $discount = $totalPrice;
                        }
                        
                        // Trừ giá trị giảm giá từ tổng giá trị đơn hàng
                        $totalPrice -= $discount;
            
                        // Lưu coupon_id vào đơn hàng
                        $order->coupon_id = $coupon->id;
                        $order->save(); 
            
                        // Trừ đi 1 lượt sử dụng mã giảm giá
                        $coupon->usage_limit -= 1;
                        if ($coupon->usage_limit <= 0) {
                            $coupon->is_active = false; 
                        }
                        $coupon->save(); 
                    } else {
                        // Nếu mã giảm giá không hợp lệ
                        return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc không đủ điều kiện sử dụng'], 400);
                    }
                } else {
                    // Nếu không tìm thấy mã giảm giá
                    return response()->json(['message' => 'Không tìm thấy mã giảm giá'], 404);
                }
            }
            
    
            // Lưu tổng giá trị đơn hàng
            $order->total_price = $totalPrice;
            $order->save(); 
    
            // Xóa các sản phẩm khỏi giỏ hàng của người dùng
            foreach ($orderItems as $item) {
                if (array_key_exists('product_id', $item)) {
                    Cart::where('user_id', $user->id)
                        ->where('product_id', $item['product_id'])
                        ->delete();
                } elseif (array_key_exists('product_variant_id', $item)) {
                    Cart::where('user_id', $user->id)
                        ->where('product_variant_id', $item['product_variant_id'])
                        ->delete();
                }
            }
             // Dispatch job để gửi email
            if (!empty($user->email)) {
                dispatch(new SendOrderSuccessMail($order));
            }
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

    public function index()
    {
        return Order::with('orderItems')->get();
    }
    public function getOrderById($id)
    {
        $order = Order::with(['user', 'orderItems.product', 'orderItems.productVariant.product']) // Eager load the related product through the product variant
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Khởi tạo mảng dữ liệu thứ tự
        $orderData = [
            'order_id' => $order->id,
            'user_name' => $order->user ? $order->user->name : 'Khách',
            'status_order' => $order->status_order,
            'payment_type' => $order->payment_type,
            'shipping_address' => $order->shipping_address,
            'phone_order' => $order->phone_order,
            'name_order' => $order->name_order,
            'email_order' => $order->email_order,
            'user_note' => $order->user_note,
            'cancel_reason' => $order->cancel_reason,

            'coupon_id' => $order->coupon_id,
            'total_price' => $order->total_price,
            'order_items' => [],
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
        ];

        for ($i = 0; $i < $order->orderItems->count(); $i++) {
            $item = $order->orderItems[$i];
            $orderData['order_items'][] = [
                'product_id' => $item->product ? $item->product->id : ($item->productVariant ? $item->productVariant->product->id : null),
                'product_name' => $item->product ? $item->product->name : ($item->productVariant ? $item->productVariant->product->name : null),
                'product_variant_id' => $item->productVariant ? $item->productVariant->id : null,
                'variant_name' => $item->productVariant ? $item->productVariant->name : null,
                'quantity' => $item->quantity,
                'price' => $item->product ? $item->product->price : ($item->productVariant ? $item->productVariant->price : 0),
                'total_item_price' => ($item->product ? $item->product->price : ($item->productVariant ? $item->productVariant->price : 0)) * $item->quantity,
            ];
        }

        return response()->json([
            'order' => $orderData,
        ], 200);
    }


    public function updateOrder(Request $request, $id)
    {
        $request->validate([
            'status_order' => 'required|string|in:Chờ xác nhận,Đã xác nhận,Đang chuẩn bị,Đang vận chuyển,Giao hàng thành công,Đã hủy',
        ]);
    
        $order = Order::find($id);
    
        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }
    
        // Define valid status transitions
        $validTransitions = [
            'Chờ xác nhận' => ['Đã xác nhận', 'Đang chuẩn bị', 'Đang vận chuyển', 'Giao hàng thành công', 'Đã hủy'],
            'Đã xác nhận' => ['Đang chuẩn bị', 'Đang vận chuyển', 'Giao hàng thành công', 'Đã hủy'],
            'Đang chuẩn bị' => ['Đang vận chuyển', 'Đã hủy'],
            'Đang vận chuyển' => ['Giao hàng thành công', 'Đã hủy'],
            'Giao hàng thành công' => [],
            'Đã hủy' => [],
        ];
    
        $currentStatus = $order->status_order;
        $newStatus = $request->input('status_order');
    
        if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
            return response()->json([
                'message' => "Không hợp lệ: không thể chuyển từ trạng thái $currentStatus sang $newStatus",
            ], 400);
        }
    
        // sửa lại số lượng hàng tồn kho nếu hủy
        if ($newStatus === 'Đã hủy' && $currentStatus !== 'Đã hủy') {
            foreach ($order->orderItems as $item) {
                // kiếm tra xem snar phẩm có bt hay ko
                if ($item->product_variant_id) {
                    // cập nhập kho cho bt
                    $variant = $item->variation;
                    if ($variant) {
                        \Log::info("Cập nhập id bt: {$variant->id}, sl: " . ($variant->quantity + $item->quantity));
                        $variant->quantity += $item->quantity;
                        $variant->save();
                    } else {
                        \Log::warning("ko tìm thấy bt nào: {$item->id}");
                    }
                } else {
                    // nếu không có biến thể sẽ cập nhập sl cho sản phẩm
                    $product = $item->product;
                    if ($product) {
                        $product->quantity += $item->quantity;
                        $product->save();
                    }
                }
            }
        }
    
        $order->status_order = $newStatus;
        $order->save();
    
        return response()->json([
            'message' => 'Cập nhật trạng thái đơn hàng thành công',
            'order' => [
                'order_id' => $order->id,
                'status_order' => $order->status_order,
            ],
        ], 200);
    }
    
    

    public function deleteOrder($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'khôgng tìm thấy đơn hàng'], 404);
        }

        if ($order->status_order !== 'Giao hàng thành công' && $order->status_order !== 'Đã hủy') {
            return response()->json(['message' => 'Chỉ có thể xóa các đơn hàng ở trạng thái "Giao hàng thành công" hoặc "Đã hủy"'], 400);
        }
        $order->delete();
        return response()->json(['message' => 'xóa thành công'], 200);
    }
    public function getOrderHistory(Request $request)
    {
        try {
            // Lấy user_id từ user đang đăng nhập
            $userId = auth()->id();
            
            // Query orders với relationship
            $orders = Order::with(['orderItems', 'orderItems.product'])
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            // Format data thủ công
            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'shipping_address' => $order->shipping_address,
                    'payment_method' => $order->payment_method,
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'subtotal' => $item->quantity * $item->price
                        ];
                    })
                ];
            });

            // Tạo response data
            $response = [
                'status' => 200,
                'message' => 'Lấy lịch sử đơn hàng thành công',
                'data' => [
                    'orders' => $formattedOrders,
                    'pagination' => [
                        'total' => $orders->total(),
                        'per_page' => $orders->perPage(),
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage()
                    ]
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // public function cancelOrder(Request $request, $order_id)
    // {
    //     $predefinedReasons = [
    //         'Người mua thay đổi ý định',
    //         'Đặt nhầm sản phẩm',
    //         'Thời gian giao hàng không phù hợp',
    //         'Không liên lạc được với cửa hàng'
    //     ];
    
    //     $order = Order::find($order_id);
    
    //     if (!$order) {
    //         return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
    //     }
    
    //     $validStatuses = ['Chờ xác nhận', 'Đang chuẩn bị', 'Đang vận chuyển'];
    //     if (!in_array($order->status_order, $validStatuses)) {
    //         return response()->json(['message' => 'Đơn hàng không thể hủy trong trạng thái này'], 400);
    //     }
    
    //     $cancelReason = $request->input('cancel_reason');
        
    //     if (!$cancelReason || !in_array($cancelReason, $predefinedReasons)) {
    //         return response()->json(['message' => 'Lý do hủy không hợp lệ'], 400);
    //     }
    
    //     $order->status_order = 'Đã hủy';
    //     $order->cancel_reason = $cancelReason;
    //     $order->save();
    
    //     return response()->json([
    //         'message' => 'Đơn hàng đã được hủy thành công',
    //         'order' => [
    //             'order_id' => $order->id,
    //             'status_order' => $order->status_order,
    //             'cancel_reason' => $order->cancel_reason,
    //         ],
    //     ], 200);
    // }



    public function cancelOrder(Request $request, $order_id)
{
    $predefinedReasons = [
        'Người mua thay đổi ý định',
        'Đặt nhầm sản phẩm',
        'Thời gian giao hàng không phù hợp',
        'Không liên lạc được với cửa hàng'
    ];

    $order = Order::find($order_id);

    if (!$order) {
        return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
    }

    $validStatuses = ['Chờ xác nhận', 'Đang chuẩn bị', 'Đang vận chuyển'];
    if (!in_array($order->status_order, $validStatuses)) {
        return response()->json(['message' => 'Đơn hàng không thể yêu cầu hủy trong trạng thái này'], 400);
    }

    $cancelReason = $request->input('cancel_reason');

    if (!$cancelReason || !in_array($cancelReason, $predefinedReasons)) {
        return response()->json(['message' => 'Lý do hủy không hợp lệ'], 400);
    }

    // Đánh dấu trạng thái "Chờ xác nhận hủy"
    $order->status_order = 'Chờ xác nhận hủy';
    $order->cancel_reason = $cancelReason;
    $order->save();

    // Gửi thông báo tới admin (tùy thuộc vào hệ thống của bạn)
    // Notification::send(Admin::all(), new OrderCancelRequestNotification($order));

    return response()->json([
        'message' => 'Yêu cầu hủy đơn hàng đã được gửi đi, đang chờ admin xác nhận',
        'order' => [
            'order_id' => $order->id,
            'status_order' => $order->status_order,
            'cancel_reason' => $order->cancel_reason,
        ],
    ], 200);
}



public function confirmCancelOrder(Request $request, $order_id)
{
    $order = Order::find($order_id);

    if (!$order) {
        return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
    }

    if ($order->status_order !== 'Chờ xác nhận hủy') {
        return response()->json(['message' => 'Không thể xác nhận hủy đơn hàng trong trạng thái này'], 400);
    }

    $action = $request->input('action'); // 'approve' hoặc 'reject'

    if ($action === 'approve') {
        $order->status_order = 'Đã hủy';
    } elseif ($action === 'reject') {
        $order->status_order = 'Chờ xác nhận'; // Quay lại trạng thái ban đầu
        $order->cancel_reason = null; // Xóa lý do hủy nếu từ chối
    } else {
        return response()->json(['message' => 'Hành động không hợp lệ'], 400);
    }

    $order->save();

    return response()->json([
        'message' => $action === 'approve' 
            ? 'Đơn hàng đã được hủy thành công' 
            : 'Yêu cầu hủy đơn hàng đã bị từ chối',
        'order' => [
            'order_id' => $order->id,
            'status_order' => $order->status_order,
        ],
    ], 200);
}

    
}