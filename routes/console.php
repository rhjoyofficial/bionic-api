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

// Process queued jobs (emails, SMS, WhatsApp, webhooks, referral commissions).
// --stop-when-empty exits after draining the queue — safe for shared hosting
// withoutOverlapping prevents multiple workers stacking up if jobs are slow
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=60')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground()
    ->description('Process queued jobs (emails, SMS, notifications)');
