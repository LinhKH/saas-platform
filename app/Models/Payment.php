<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
  protected $fillable = [
    'gateway',
    'order_id',
    'access_id',
    'access_pass',
    'user_id',
    'amount',
    'status',
    'purpose',
    'subscription_id',
    'raw_result',
  ];

  protected $casts = [
    'raw_result' => 'array',
  ];
}
