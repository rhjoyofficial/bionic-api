<?php

namespace App\Infrastructure\WhatsApp;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
  public function sendText($phone, $message)
  {
    $url = config('whatsapp.url') .
      '/' . config('whatsapp.phone_number_id') .
      '/messages';

    return Http::withToken(config('whatsapp.token'))
      ->post($url, [

        'messaging_product' => 'whatsapp',

        'to' => $phone,

        'type' => 'text',

        'text' => [
          'body' => $message
        ]

      ]);
  }
}
