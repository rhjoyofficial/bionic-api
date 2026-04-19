<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusPushNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class OrderStatusNotificationListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $user = $event->order->user;

        if (!$user) return;

        // Skip if FCM is not configured (placeholder key or missing)
        $key = config('firebase.server_key');
        if (empty($key) || $key === 'your_firebase_server_key') return;

        Notification::send(
            $user,
            new OrderStatusPushNotification($event->order)
        );
    }
}
