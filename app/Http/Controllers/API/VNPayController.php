<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
class VNPayController extends Controller
{
    //
    public function createPayment(Request $request,Order $order)
    {

        // Lấy thông tin cấu hình từ .env
        $vnp_TmnCode = env('VNP_TMN_CODE'); // Mã website tại VNPay
        $vnp_HashSecret = env('VNP_HASH_SECRET'); // Chuỗi bí mật
        $vnp_Url = env('VNP_URL'); // URL thanh toán
        $vnp_Returnurl = env('VNP_RETURN_URL'); // URL quay lại sau khi thanh toán

        // Dữ liệu thanh toán
        $vnp_TxnRef = time(); // Mã giao dịch thanh toán, unique mỗi lần
        $vnp_OrderInfo = ' Thanh toán đơn hàng ' . $order->id;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total_price; // Số tiền, tính bằng VND * 100
        $vnp_Locale = 'vn'; // Ngôn ngữ
        $vnp_BankCode = $request->input('bank_code') ?? ''; // Chọn ngân hàng nếu có

        // Tạo dữ liệu đầu vào cho thanh toán
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => request()->ip(),
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        // Nếu có mã ngân hàng, thêm vào inputData
        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        // Ký mã hóa dữ liệu và tạo URL thanh toán
        ksort($inputData);
        $hashdata = urldecode(http_build_query($inputData));
        $query = http_build_query($inputData);
        $vnp_Url = $vnp_Url . "?" . $query;

        if ($vnp_HashSecret) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
        }

        // Trả về link thanh toán qua JSON
        return response()->json([
            'status' => 'success',
            'payment_url' => $vnp_Url
        ]);
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET'); // Chuỗi bí mật

        $inputData = [];
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = urldecode(http_build_query($inputData));

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            if ($inputData['vnp_ResponseCode'] == '00') {
                $order = Order::create([
                    'transaction_id' => $inputData['vnp_TxnRef'],
                    'amount' => $inputData['vnp_Amount'], // Convert from VND to appropriate currency
                    'payment_method' => 'VNPay',
                    'status' => 'Đã thanh toán',
                    // Thêm các trường dữ liệu khác liên quan đến đơn hàng tùy theo yêu cầu
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Giao dịch thành công',
                    'data' => [
                        'order' => $order->toArray()
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Giao dịch không thành công',
                    'data' => $inputData
                ]);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Sai chữ ký bảo mật',
            ]);
        }
    }

}
