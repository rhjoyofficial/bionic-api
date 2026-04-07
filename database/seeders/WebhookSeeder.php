<?php

namespace Database\Seeders;

use App\Domains\Webhook\Models\Webhook;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebhookSeeder extends Seeder
{
    public function run(): void
    {
        $hooks = [
            [
                'event' => 'order.created',
                'url' => 'https://example.com/webhooks/order-created',
                'secret' => Str::random(32),
                'is_active' => false,
            ],
            [
                'event' => 'order.status.changed',
                'url' => 'https://example.com/webhooks/order-status',
                'secret' => Str::random(32),
                'is_active' => false,
            ],
        ];

        foreach ($hooks as $hook) {
            Webhook::updateOrCreate(
                ['event' => $hook['event'], 'url' => $hook['url']],
                $hook
            );
        }
    }
}
