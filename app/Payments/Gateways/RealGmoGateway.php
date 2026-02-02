<?php

namespace App\Payments\Gateways;

use Illuminate\Support\Facades\Http;
use App\Payments\Contracts\GmoGatewayInterface;
use App\Payments\DTOs\GmoResult;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RealGmoGateway implements GmoGatewayInterface
{
  protected string $shopId;
  protected string $shopPass;

  public function __construct()
  {
    $this->shopId   = config('services.gmo.shop_id');
    $this->shopPass = config('services.gmo.shop_pass');
  }

  /* ---------------------------
       ENTRY TRAN
    ----------------------------*/
  public function entryTran(string $orderId, int $amount): array
  {
    $response = Http::asForm()->post(
      config('services.gmo.entry_url'),
      [
        'ShopID'  => $this->shopId,
        'ShopPass' => $this->shopPass,
        'OrderID' => $orderId,
        'Amount'  => $amount,
      ]
    )->body();

    parse_str($response, $data);

    if (isset($data['ErrCode'])) {
      throw new \RuntimeException('GMO EntryTran failed: ' . $response);
    }

    return [
      'AccessID'   => $data['AccessID'],
      'AccessPass' => $data['AccessPass'],
    ];
  }

  /* ---------------------------
       EXEC TRAN
    ----------------------------*/
  public function execTran(string $accessId, string $accessPass): array
  {
    return [
      'payment_url' => config('services.gmo.exec_url') . '?' . http_build_query([
        'AccessID'   => $accessId,
        'AccessPass' => $accessPass,
        'ShopID'     => $this->shopId,
      ]),
    ];
  }

  /* ---------------------------
       SEARCH TRADE (POLLING)
    ----------------------------*/
  public function searchTrade(string $orderId): array
  {
    $response = Http::asForm()->post(
      config('services.gmo.search_url'),
      [
        'ShopID'   => $this->shopId,
        'ShopPass' => $this->shopPass,
        'OrderID'  => $orderId,
      ]
    )->body();

    parse_str($response, $data);

    return $data;
  }

  /* ---------------------------
       VERIFY + PARSE CALLBACK
    ----------------------------*/
  public function verifyAndParseResult(array $payload): GmoResult
  {
    if (!$this->verifySignature($payload)) {
      throw new AccessDeniedHttpException('Invalid GMO signature');
    }

    return new GmoResult(
      orderId: $payload['OrderID'],
      success: in_array($payload['Status'], ['CAPTURE', 'SUCCESS']),
      raw: $payload
    );
  }

  /* ---------------------------
       GMO SIGNATURE (MD5)
2️⃣ SIGNATURE MD5 — NHỮNG ĐIỀU TUYỆT ĐỐI KHÔNG ĐƯỢC SAI
✅ PHẢI

OrderID ASCII

Amount integer

Thứ tự: ShopID + OrderID + Amount + ShopPass

Hash từ raw payload

hash_equals()

❌ KHÔNG

trim / cast trước khi hash

dùng decimal amount

đổi thứ tự field

verify sau khi parse business
    ----------------------------*/
  protected function verifySignature(array $payload): bool
  {
    // GMO callback thường gửi Hash hoặc CheckString
    if (!isset($payload['OrderID'], $payload['Amount'], $payload['CheckString'])) {
      return false;
    }

    $expected = md5(
      $this->shopId .
        $payload['OrderID'] .
        $payload['Amount'] .
        $this->shopPass
    );

    return hash_equals($expected, $payload['CheckString']);
  }
}
