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

        // Guest orders have no user — skip push notification
        if (!$user) return;

        Notification::send(
            $user,
            new OrderStatusPushNotification($event->order)
        );
    }
}
