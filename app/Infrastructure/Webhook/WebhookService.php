<?php

namespace App\Infrastructure\Webhook;

use App\Domains\Webhook\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
  public function dispatch(string $event, $payload)
  {
    $webhooks = Webhook::where('event', $event)
      ->where('is_active', true)
      ->get();

    foreach ($webhooks as $hook) {

      if (!$hook->secret) {
        Log::warning("Webhook #{$hook->id} has no secret configured — skipping dispatch.", [
          'event' => $event,
          'url'   => $hook->url,
        ]);
        continue;
      }

      $signature = hash_hmac(
        'sha256',
        json_encode($payload),
        $hook->secret
      );

      Http::withHeaders([
        'X-Bionic-Signature' => $signature
      ])->post($hook->url, [

        'event' => $event,
        'data' => $payload

      ]);
    }
  }
}
