<?php

namespace App\Jobs;

use App\Infrastructure\Webhook\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWebhookJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int   $tries   = 3;
    public array $backoff = [30, 120, 300];
    public int   $timeout = 15;

    public function __construct(
        public readonly string $event,
        public readonly mixed  $payload,
    ) {}

    public function handle(WebhookService $service): void
    {
        $service->dispatch($this->event, $this->payload);
    }

    public function failed(Throwable $e): void
    {
        Log::error("SendWebhookJob permanently failed for event [{$this->event}]", [
            'error' => $e->getMessage(),
        ]);
    }
}
