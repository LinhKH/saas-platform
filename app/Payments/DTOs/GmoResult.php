<?php

namespace App\Payments\DTOs;

// 👉 Callback & Polling dùng chung DTO này
class GmoResult
{
  public function __construct(
    public string $orderId,
    public bool $success,
    public array $raw
  ) {}
}
