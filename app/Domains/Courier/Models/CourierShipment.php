<?php

namespace App\Domains\Courier\Models;

use Illuminate\Database\Eloquent\Model;

class CourierShipment extends Model
{
  protected $fillable = [

    'order_id',
    'courier',
    'tracking_code',
    'status'

  ];

  public function order()
  {
    return $this->belongsTo(
      \App\Domains\Order\Models\Order::class
    );
  }
}
