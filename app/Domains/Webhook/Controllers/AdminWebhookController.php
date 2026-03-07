<?php

namespace App\Domains\Webhook\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Webhook\Models\Webhook;
use Illuminate\Http\Request;

class AdminWebhookController extends Controller
{
  public function index()
  {
    return Webhook::all();
  }

  public function store(Request $request)
  {
    return Webhook::create($request->validate([

      'event' => 'required',

      'url' => 'required|url',

      'secret' => 'nullable'

    ]));
  }

  public function destroy(Webhook $webhook)
  {
    $webhook->delete();
  }
}
