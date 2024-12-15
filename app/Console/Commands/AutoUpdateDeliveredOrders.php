<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoUpdateDeliveredOrders extends Command
{
    /**
     * The name and description of the command.
     */
    protected $signature = 'orders:auto-update';

    protected $description = 'Tự động cập nhật trạng thái đơn hàng sang "Đã nhận hàng" sau 10 giây nếu chưa xác nhận.';

    /**
     * Execute the command.
     */
    public function handle()
    {
       // Lấy thời gian hiện tại
        $now = Carbon::now();

        // Tìm đơn hàng có trạng thái "Giao hàng thành công" và đã ở trạng thái đó trong hơn 3 ngày
        $orders = Order::where('status_order', 'Giao hàng thành công')
            ->where('updated_at', '<', $now->subDays(3))
            ->get();

        // Lặp qua từng lệnh
        foreach ($orders as $order) {
            // Thêm độ trễ 5p giây trước khi cập nhật trạng thái đơn hàng
            sleep(10);

            $order->update(['status_order' => 'Đã nhận hàng']);

            // Ghi lại hành động cập nhật
            Log::info("Đã cập nhật ID đơn hàng {$order->id} to 'Đã nhận hàng' sau 300 giây trì hoãn.");
        }

        // Ghi lại tổng số đơn hàng đã cập nhật
        $this->info("Cập nhật thành công {$orders->count()} đơn hàng.");
    }
}
