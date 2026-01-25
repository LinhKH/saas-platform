<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

// 👉 Controller giờ chỉ còn đúng vai trò HTTP.
class AuthController extends BaseApiController
{
  public function __construct(
    private AuthService $authService
  ) {}

  public function register(Request $request)
  {
    $data = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users',
      'password' => 'required|min:8',
    ]);

    $user = $this->authService->register($data);

    return $this->success($user, 'Registered');
  }

  public function login(Request $request)
  {
    $data = $request->validate([
      'email' => 'required|email',
      'password' => 'required',
      'device_name' => 'required|string',
    ]);

    $token = $this->authService->login(
      $data['email'],
      $data['password'],
      $data['device_name']
    );

    return $this->success(['token' => $token], 'Logged in');
  }

  public function logout(Request $request)
  {
    $this->authService->logout($request->user());

    return $this->success(null, 'Logged out');
  }
}
