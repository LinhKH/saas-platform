<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\Auth\AuthService;

class VerifyEmailController extends BaseApiController
{
  public function __construct(
    private AuthService $authService
  ) {}

  public function verify(Request $request)
  {
    $this->authService->verifyEmail(
      $request->route('id'),
      $request->route('hash')
    );

    return $this->success(null, 'Email verified');
  }
}
