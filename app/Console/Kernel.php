<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */


     protected function schedule(Schedule $schedule)
     {
         $schedule->call(function () {
             app('App\Http\Controllers\OrderController')->autoConfirmDelivery();
         })->hourly();  // Lên lịch cho hàm autoConfirmDelivery chạy hàng ngày
     }
     
     
     

    /**
     * Register the commands for the application.php artisan schedule:list
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    
}
