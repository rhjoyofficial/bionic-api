<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Webhook\WebhookService;

class SendWebhookJob implements ShouldQueue
{
    public $event;
    public $payload;

    public function __construct($event, $payload)
    {
        $this->event = $event;
        $this->payload = $payload;
    }

    public function handle(WebhookService $service)
    {
        $service->dispatch(
            $this->event,
            $this->payload
        );
    }
}
