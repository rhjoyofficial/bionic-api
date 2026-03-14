<?php

namespace App\Services\Webhook;

use App\Domains\Webhook\Models\Webhook;
use Illuminate\Support\Facades\Http;

class WebhookService
{
  public function dispatch(string $event, $payload)
  {
    $webhooks = Webhook::where('event', $event)
      ->where('is_active', true)
      ->get();

    foreach ($webhooks as $hook) {

      $signature = hash_hmac(
        'sha256',
        json_encode($payload),
        $hook->secret ?? 'bionic-default'
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
