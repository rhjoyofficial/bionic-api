<?php

namespace App\Domains\Webhook\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
  protected $fillable = [

    'event',
    'url',
    'secret',
    'is_active'

  ];
}
