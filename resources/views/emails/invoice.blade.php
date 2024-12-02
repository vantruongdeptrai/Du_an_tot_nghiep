<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn đơn hàng #{{ $order->id }}</title>
</head>
<body>
    <h1>Hóa đơn của bạn - Đơn hàng #{{ $order->id }}</h1>
    <p>Xin chào {{ $order->user_name }},</p>
    <p>Cảm ơn bạn đã mua sắm tại cửa hàng của chúng tôi. Dưới đây là thông tin đơn hàng của bạn:</p>
    <table border="1">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Giá</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 0, ',', '.') }} VND</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p><strong>Tổng giá trị đơn hàng:</strong> {{ number_format($order->total_price, 0, ',', '.') }} VND</p>
    <p>Trân trọng,</p>
    <p>Cửa hàng của chúng tôi</p>
</body>
</html>
