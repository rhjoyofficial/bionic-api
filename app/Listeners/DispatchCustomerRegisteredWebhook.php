<?php

namespace App\Listeners;

use App\Events\CustomerRegistered;
use App\Jobs\SendWebhookJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchCustomerRegisteredWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public bool  $afterCommit = true;
    public int   $tries       = 3;
    public array $backoff      = [30, 120, 300];

    public function handle(CustomerRegistered $event): void
    {
        $user = $event->user;

        SendWebhookJob::dispatch('customer.registered', [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'referral_code' => $user->referral_code,
            'registered_at' => $user->created_at?->toISOString(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('DispatchCustomerRegisteredWebhook failed: ' . $e->getMessage());
    }
}
