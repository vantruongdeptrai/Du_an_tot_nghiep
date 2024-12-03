<!DOCTYPE html>
<html>
<head>
    <title>Đơn hàng thành công</title>
</head>
<body>
    <h1>Xin chào, {{ $order['customer_name'] }}</h1>
    <p>Cảm ơn bạn đã đặt hàng tại {{ config('app.name') }}.</p>
    <p>Thông tin đơn hàng:</p>
    <ul>
        <li><strong>Mã đơn hàng:</strong> {{ $order['order_id'] }}</li>
        <li><strong>Tổng tiền:</strong> {{ number_format($order['total_price'], 0, ',', '.') }} VND</li>
    </ul>
    <p>Cảm ơn bạn đã mua hàng của chuúng tôi!</p>
</body>
</html>
