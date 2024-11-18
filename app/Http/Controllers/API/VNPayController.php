<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller; // Import lớp Controller

class VNPayController extends Controller
{
    public function createPayment(Request $request)
    {
        // Get data from request
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "https://localhost/vnpay_php/vnpay_return.php";
        $vnp_TmnCode = "YOUR_TMN_CODE"; // Replace with your VNPAY merchant code
        $vnp_HashSecret = "YOUR_SECRET_KEY"; // Replace with your VNPAY secret key

        $vnp_TxnRef = $request->input('order_id');
        $vnp_OrderInfo = $request->input('order_desc');
        $vnp_OrderType = $request->input('order_type');
        $vnp_Amount = $request->input('amount') * 100; // Amount in VND (note: it's multiplied by 100)
        $vnp_Locale = $request->input('language');
        $vnp_BankCode = $request->input('bank_code');
        $vnp_IpAddr = $request->ip();

        // Billing Information
        $vnp_Bill_Mobile = $request->input('txt_billing_mobile');
        $vnp_Bill_Email = $request->input('txt_billing_email');
        $fullName = trim($request->input('txt_billing_fullname'));
        $name = explode(' ', $fullName);
        $vnp_Bill_FirstName = array_shift($name);
        $vnp_Bill_LastName = array_pop($name);
        $vnp_Bill_Address = $request->input('txt_inv_addr1');
        $vnp_Bill_City = $request->input('txt_bill_city');
        $vnp_Bill_Country = $request->input('txt_bill_country');
        $vnp_Bill_State = $request->input('txt_bill_state');

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
            "vnp_Bill_Mobile" => $vnp_Bill_Mobile,
            "vnp_Bill_Email" => $vnp_Bill_Email,
            "vnp_Bill_FirstName" => $vnp_Bill_FirstName,
            "vnp_Bill_LastName" => $vnp_Bill_LastName,
            "vnp_Bill_Address" => $vnp_Bill_Address,
            "vnp_Bill_City" => $vnp_Bill_City,
            "vnp_Bill_Country" => $vnp_Bill_Country,
        ];

        if ($vnp_BankCode) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if ($vnp_Bill_State) {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
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

        // Build the secure hash
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $query .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Final URL
        $vnp_Url .= "?" . $query;

        // Return the URL for redirect or success message
        return response()->json([
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url
        ]);
    }

public function handleIPN(Request $request)
{
    $vnp_TmnCode = env('VNP_TMN_CODE'); // Get from .env
    $vnp_HashSecret = env('VNP_HASH_SECRET'); // Get from .env

    // Get VNPAY response data
    $vnp_ResponseCode = $request->input('vnp_ResponseCode');
    $vnp_TransactionStatus = $request->input('vnp_TransactionStatus');
    $vnp_Amount = $request->input('vnp_Amount');
    $vnp_TxnRef = $request->input('vnp_TxnRef');
    $vnp_SecureHash = $request->input('vnp_SecureHash');
    $vnp_OrderInfo = $request->input('vnp_OrderInfo');
    $vnp_TransactionNo = $request->input('vnp_TransactionNo');
    $vnp_BankCode = $request->input('vnp_BankCode');

    // Prepare data for security check
    $inputData = $request->except('vnp_SecureHash'); // Exclude the secure hash field
    ksort($inputData); // Sort the parameters

    $hashData = '';
    foreach ($inputData as $key => $value) {
        $hashData .= urlencode($key) . "=" . urlencode($value) . '&';
    }
    $hashData = rtrim($hashData, '&');

    // Validate Secure Hash
    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    
    // Check if secure hash is valid
    if ($vnp_SecureHash !== $secureHash) {
        Log::error('Invalid secure hash received from VNPAY', ['request' => $request->all()]);
        return response('97|Invalid Secure Hash', 200); // Respond with error code
    }

    // Hash is valid, proceed with transaction processing
    if ($vnp_ResponseCode == '00' && $vnp_TransactionStatus == '00') {
        // Successful payment
        $this->updatePaymentStatus($vnp_TxnRef, 'success', $vnp_Amount);
        return response('00|Success', 200); // Respond with success code to VNPAY
    } else {
        // Payment failed or declined
        $this->updatePaymentStatus($vnp_TxnRef, 'failed', $vnp_Amount);
        return response('99|Failed', 200); // Respond with failure code to VNPAY
    }
    }

}



