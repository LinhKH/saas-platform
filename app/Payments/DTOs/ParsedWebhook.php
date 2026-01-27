<?php

namespace App\Payments\DTOs;
// DTO là gì?
// Data Transfer Object - một đối tượng đơn giản dùng để truyền dữ liệu giữa các phần của ứng dụng mà không có logic nghiệp vụ phức tạp bên trong.
// Data Transfer Object for parsed webhook data
class ParsedWebhook
{
  public function __construct(
    public string $gatewayPaymentId,
    public string $status, // succeeded | failed
    public array $rawPayload
  ) {}
}
/**
👉 Dùng DTO giúp:

Không phụ thuộc cấu trúc webhook

Dễ test
 */