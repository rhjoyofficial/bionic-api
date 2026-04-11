<?php

use App\Console\Commands\AbandonExpiredCarts;
use App\Console\Commands\ExpireCoupons;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Deactivate coupons whose end_date has passed — runs every day at midnight
Schedule::command(ExpireCoupons::class)
    ->daily()
    ->description('Deactivate expired coupons');

// Release reserved stock for abandoned/expired guest carts — runs every hour
Schedule::command(AbandonExpiredCarts::class)
    ->hourly()
    ->description('Release reserved stock from expired guest carts');
