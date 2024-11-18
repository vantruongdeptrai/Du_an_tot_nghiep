<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
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
        $vnp_TxnRef = $order->id; // Mã giao dịch thanh toán, unique mỗi lần
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
    const STATUS_PAYMENT_PAID = 'paid';
    public function vnpayReturn(Request $request)
    {
        
        try {
            $vnp_HashSecret = env('VNP_HASH_SECRET'); // Chuỗi bí mật từ môi trường
            $inputData = [];
    
            // Lọc dữ liệu bắt đầu với "vnp_"
            foreach ($request->all() as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }
    
            // Lấy Secure Hash và xóa khỏi dữ liệu để kiểm tra
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? null;
            unset($inputData['vnp_SecureHash']);
            ksort($inputData); // Sắp xếp theo thứ tự key
    
            // Tạo hash để so sánh
            $hashData = urldecode(http_build_query($inputData));
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    
            // Kiểm tra chữ ký bảo mật
            if ($secureHash !== $vnp_SecureHash) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Sai chữ ký bảo mật',
                ], 400);
            }
    
            // Xử lý nếu giao dịch thành công
            if ($inputData['vnp_ResponseCode'] === '00') {
                $orderId = $inputData['vnp_TxnRef']; // Mã đơn hàng
                $order = Order::findOrFail($orderId); // Tìm đơn hàng theo ID
    
                // Tạo giao dịch
                $transaction = Transaction::create([
                    'order_id' => $orderId,
                    'transaction_id' => $inputData['vnp_TransactionNo'],
                    'amount' => $inputData['vnp_Amount'] / 100, // Chuyển đổi từ đồng sang đơn vị phù hợp
                    'status' => 'completed',
                ]);
    
                // Cập nhật trạng thái đơn hàng
                
                $order->status_payment = 'Đã thanh toán';
                $order->save();
    
                // Trả về phản hồi thành công
                return response()->json([
                    'status' => 'success',
                    'message' => 'Giao dịch thành công',
                    'data' => [
                        'order' => $order->toArray(),
                        'transaction' => $transaction->toArray(),
                    ]
                ]);
            }
    
            // Nếu giao dịch không thành công
            return response()->json([
                'status' => 'fail',
                'message' => 'Giao dịch không thành công',
                'data' => $inputData,
            ], 400);
    
        } catch (\Exception $e) {
            // Xử lý ngoại lệ và trả về phản hồi lỗi
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi trong quá trình xử lý',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
