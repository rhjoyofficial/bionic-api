<?php

namespace App\Infrastructure\SMS;

use Illuminate\Support\Facades\Http;

class SMSService
{
  public function send(string $phone, string $message)
  {
    $response = Http::post(config('sms.url'), [

      'token' => config('sms.token'),

      'to' => $phone,

      'message' => $message,

      'sender' => config('sms.sender'),

    ]);

    return $response->json();
  }
}
