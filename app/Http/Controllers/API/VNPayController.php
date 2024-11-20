<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller; 
use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Cart;

class VNPayController extends Controller
{
    public function createPayment(Request $request)
{
    $user = User::find($request->input('user_id'));
    if (!$user) {
        return response()->json(['message' => 'Không có người dùng'], 401);
    }

    DB::beginTransaction();
    try {
        $totalPriceProduct = 0;
        $totalPriceVariant = 0;
        $orderItems = $request->input('order_items');

        if (empty($orderItems)) {
            throw new \Exception('Không có sản phẩm trong đơn hàng.');
        }

        $order = Order::create([
            'user_id' => $user->id,
            'status_order' => 'Chờ xác nhận',
            'payment_type' => $request->input('payment_type'),
            'shipping_address' => $request->input('shipping_address'),
            'user_note' => $request->input('user_note', null),
            'coupon_id' => $request->input('coupon_id', null),
            'phone_order' => $request->input('phone_order'),
            'name_order' => $request->input('name_order'),
            'email_order' => $request->input('email_order'),
            'total_price' => 0,
        ]);

        foreach ($orderItems as $item) {
            if (isset($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    throw new \Exception('Không tìm thấy sản phẩm: ' . $item['product_id']);
                }

                if ($product->quantity < $item['quantity']) {
                    throw new \Exception('Số lượng sản phẩm không đủ: ' . $item['product_id']);
                }

                $price = $product->sale_price && $product->sale_start <= now() && $product->sale_end >= now()
                    ? $product->sale_price
                    : $product->price;

                $totalPriceProduct += $price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => null,
                    'quantity' => $item['quantity'],
                ]);
            } elseif (isset($item['product_variant_id'])) {
                $productVariant = ProductVariant::find($item['product_variant_id']);
                if (!$productVariant) {
                    throw new \Exception('Không có sản phẩm biến thể: ' . $item['product_variant_id']);
                }

                if ($productVariant->quantity < $item['quantity']) {
                    throw new \Exception('Số lượng sản phẩm biến thể không đủ: ' . $item['product_variant_id']);
                }

                $price = $productVariant->sale_price && $productVariant->sale_start <= now() && $productVariant->sale_end >= now()
                    ? $productVariant->sale_price
                    : $productVariant->price;

                $totalPriceVariant += $price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productVariant->product_id, 
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        }

        $totalAmount = $totalPriceProduct + $totalPriceVariant;

        $order->update(['total_price' => $totalAmount]);

        $paymentUrl = $this->createPaymentUrl($request, $totalPriceProduct, $totalPriceVariant, $order->id);

        DB::commit();

        return response()->json([
            'message' => 'Đơn hàng đã được tạo thành công!',
            'payment_url' => $paymentUrl,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Lỗi khi xử lý thanh toán: ' . $e->getMessage()], 500);
    }
}

private function createPaymentUrl(Request $request, $totalPriceProduct, $totalPriceVariant, $orderId)
{
    $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    $vnp_Returnurl = "https://www.your-website.com/payment-return"; 
    $vnp_TmnCode = "X9G2TVDO"; 
    $vnp_HashSecret = "OZKZBQ6BPVH2196YDDPJHYBIDUWH7J10"; 

    $vnp_TxnRef = $orderId; 
    $vnp_OrderInfo = "Thanh toán đơn hàng #" . $vnp_TxnRef;
    $vnp_OrderType = "billpayment"; 
    $vnp_Amount = ($totalPriceProduct + $totalPriceVariant) * 100; 
    $vnp_Locale = 'vn'; 
    $vnp_BankCode = $request->input('payment_type'); 
    $vnp_IpAddr = request()->ip(); // User's IP

    $inputData = [
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => now()->format('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef,
    ];

    if ($vnp_BankCode) {
        $inputData['vnp_BankCode'] = $vnp_BankCode;
    }

    ksort($inputData);
    $query = "";
    $i = 0;
    $hashdata = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }

    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $query .= 'vnp_SecureHash=' . $vnpSecureHash;

    return $vnp_Url . "?" . $query;
}

    
    

public function handleIPN(Request $request)
{
    $vnp_TmnCode = env('VNP_TMN_CODE'); 
    $vnp_HashSecret = env('VNP_HASH_SECRET'); 

    // Get VNPAY response data
    $vnp_ResponseCode = $request->input('vnp_ResponseCode');
    $vnp_TransactionStatus = $request->input('vnp_TransactionStatus');
    $vnp_Amount = $request->input('vnp_Amount');
    $vnp_TxnRef = $request->input('vnp_TxnRef');
    $vnp_SecureHash = $request->input('vnp_SecureHash');
    $vnp_OrderInfo = $request->input('vnp_OrderInfo');
    $vnp_TransactionNo = $request->input('vnp_TransactionNo');
    $vnp_BankCode = $request->input('vnp_BankCode');

    $inputData = $request->except('vnp_SecureHash'); 
    ksort($inputData); 

    $hashData = '';
    foreach ($inputData as $key => $value) {
        $hashData .= urlencode($key) . "=" . urlencode($value) . '&';
    }
    $hashData = rtrim($hashData, '&');

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    
    if ($vnp_SecureHash !== $secureHash) {
        Log::error('Invalid secure hash received from VNPAY', ['request' => $request->all()]);
        return response('97|Invalid Secure Hash', 200); 
    }

    if ($vnp_ResponseCode == '00' && $vnp_TransactionStatus == '00') {
        $this->updatePaymentStatus($vnp_TxnRef, 'success', $vnp_Amount);
        return response('00|Success', 200); 
    } else {
        $this->updatePaymentStatus($vnp_TxnRef, 'failed', $vnp_Amount);
        return response('99|Failed', 200); 
    }
    }

    

}



