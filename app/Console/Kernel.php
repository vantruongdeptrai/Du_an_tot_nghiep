<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */


    protected function schedule(Schedule $schedule): void
    {
        // Gọi phương thức tự động xác nhận giao hàng hàng ngày
        $schedule->call('App\Http\Controllers\OrderController@autoConfirmDelivery')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    
}
