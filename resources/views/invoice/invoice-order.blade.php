<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1.6;
        }
        .invoice-header {
            text-align: center;
        }
        .invoice-details {
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1>HÓA ĐƠN</h1>
        <p>Công ty: {{ config('app.name') }}</p>
        <p>Ngày: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</p>
    </div>

    <div class="invoice-details">
        <p><strong>Khách hàng:</strong> {{ $order->customer_name }}</p>
        <p><strong>Email:</strong> {{ $order->email }}</p>
        <p><strong>Mã đơn hàng:</strong> {{ $order->order_id }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Tổng</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 0, ',', '.') }} VND</td>
                    <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} VND</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Tổng cộng</th>
                <th>{{ number_format($order->total_price, 0, ',', '.') }} VND</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
