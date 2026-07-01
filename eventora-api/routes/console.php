<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Order;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:release-expired')->everyMinute();

// Send Daily Sales Summary at 08:00 AM
Schedule::command('app:send-daily-sales')->dailyAt('08:00');
