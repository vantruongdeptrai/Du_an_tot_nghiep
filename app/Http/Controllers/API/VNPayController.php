<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Order;
class VNPayController extends Controller
{
    //
    public function createPayment(Request $request, Order $order)
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "https://localhost/vnpay_php/vnpay_return.php";
        $vnp_TmnCode = "HSKYRL0D"; // Mã website tại VNPAY
        $vnp_HashSecret = "WC3GP5NHLZE23I5EG3VHOAMRX6JJ0OI6"; // Chuỗi bí mật

        // Lấy dữ liệu từ request
        $validatedData = $request->validate([
            'order_id' => 'required|string',
            'order_desc' => 'required|string',
            'order_type' => 'required|string',
            'amount' => 'required|numeric',
            'language' => 'required|string',
            'bank_code' => 'nullable|string',
            'txtexpire' => 'nullable|string',
            'txt_billing_mobile' => 'nullable|string',
            'txt_billing_email' => 'nullable|email',
            'txt_billing_fullname' => 'nullable|string',
            'txt_inv_addr1' => 'nullable|string',
            'txt_bill_city' => 'nullable|string',
            'txt_bill_country' => 'nullable|string',
            'txt_bill_state' => 'nullable|string',
            'txt_inv_mobile' => 'nullable|string',
            'txt_inv_email' => 'nullable|email',
            'txt_inv_customer' => 'nullable|string',
            'txt_inv_company' => 'nullable|string',
            'txt_inv_taxcode' => 'nullable|string',
            'cbo_inv_type' => 'nullable|string',
        ]);

        // Map dữ liệu
        $vnp_TxnRef = $validatedData['order_id'];
        $vnp_OrderInfo = $validatedData['order_desc'];
        $vnp_OrderType = $validatedData['order_type'];
        $vnp_Amount = $validatedData['amount'] * 100;
        $vnp_Locale = $validatedData['language'];
        $vnp_BankCode = $validatedData['bank_code'] ?? null;
        $vnp_IpAddr = $request->ip();
        $vnp_ExpireDate = $validatedData['txtexpire'] ?? null;

        // Billing
        $fullName = $validatedData['txt_billing_fullname'] ?? '';
        $name = $fullName ? explode(' ', $fullName) : [];
        $vnp_Bill_FirstName = $name[0] ?? '';
        $vnp_Bill_LastName = end($name) ?: '';

        // Tạo dữ liệu cho VNPAY
        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate,
            "vnp_Bill_Mobile" => $validatedData['txt_billing_mobile'] ?? '',
            "vnp_Bill_Email" => $validatedData['txt_billing_email'] ?? '',
            "vnp_Bill_FirstName" => $vnp_Bill_FirstName,
            "vnp_Bill_LastName" => $vnp_Bill_LastName,
            "vnp_Bill_Address" => $validatedData['txt_inv_addr1'] ?? '',
            "vnp_Bill_City" => $validatedData['txt_bill_city'] ?? '',
            "vnp_Bill_Country" => $validatedData['txt_bill_country'] ?? '',
            "vnp_Inv_Phone" => $validatedData['txt_inv_mobile'] ?? '',
            "vnp_Inv_Email" => $validatedData['txt_inv_email'] ?? '',
            "vnp_Inv_Customer" => $validatedData['txt_inv_customer'] ?? '',
            "vnp_Inv_Company" => $validatedData['txt_inv_company'] ?? '',
            "vnp_Inv_Taxcode" => $validatedData['txt_inv_taxcode'] ?? '',
            "vnp_Inv_Type" => $validatedData['cbo_inv_type'] ?? '',
        ];

        if ($vnp_BankCode) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $hashData = urldecode(http_build_query($inputData));
        $vnpSecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $vnp_Url = $vnp_Url . "?" . http_build_query($inputData) . '&vnp_SecureHash=' . $vnpSecureHash;

        $returnData = [
            'code' => '00',
            'message' => 'Transaction created successfully',
            'data' => [
                'payment_url' => $vnp_Url,
                'transaction_ref' => $vnp_TxnRef,
                'amount' => $vnp_Amount / 100,
                'order_info' => $vnp_OrderInfo,
            ],
        ];

        return response()->json($returnData);
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
