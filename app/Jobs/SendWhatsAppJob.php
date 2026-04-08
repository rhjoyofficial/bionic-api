<?php

namespace App\Jobs;

use App\Infrastructure\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Queue\InteractsWithQueue;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels, InteractsWithQueue;

    protected $phone;
    protected $message;
    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 15;

    public function __construct($phone, $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle(WhatsAppService $service)
    {
        $service->sendText(
            $this->phone,
            $this->message
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('WhatsApp notification failed permanently', [
            'phone'   => $this->phone,
            'message' => $this->message,
            'error'   => $exception->getMessage(),
        ]);
    }
}
