<?php

namespace App\Services\Auth;

use App\Exceptions\DomainException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

/**
🧠 Nhận xét senior

AuthService không biết HTTP

Throw DomainException

Test được dễ dàng

Sau này thay Sanctum → không đụng Controller
 */

class AuthService
{
  public function __construct(
    private UserRepositoryInterface $userRepo
  ) {}

  public function register(array $data): User
  {
    return $this->userRepo->create($data);
  }

  public function login(
    string $email,
    string $password,
    string $deviceName
  ): string {
    $user = $this->userRepo->findByEmail($email);

    if (!$user || !Hash::check($password, $user->password)) {
      throw new DomainException('Invalid credentials', 401);
    }

    return $user->createToken($deviceName)->plainTextToken;
  }

  public function logout(User $user): void
  {
    $user->currentAccessToken()->delete();
  }
}
