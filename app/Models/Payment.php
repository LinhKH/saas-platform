<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
  protected $fillable = [
    'gateway',
    'gateway_payment_id',
    'reference',
    'amount',
    'status',
    'payload',
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'payload' => 'array',
  ];
}
