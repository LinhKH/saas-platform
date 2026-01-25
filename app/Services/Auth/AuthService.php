<?php

namespace App\Services\Auth;

use App\Exceptions\DomainException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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
    $user = $this->userRepo->create($data);
    event(new \App\Events\UserRegistered($user));
    return $user;
  }

  protected function throttleKey(string $email, string $ip): string
  {
    return Str::lower($email) . '|' . $ip;
  }

  public function login(
    string $email,
    string $password,
    string $deviceName,
    string $ip
  ): string {
    // throttleKey là một chuỗi duy nhất được tạo từ email và IP để phân biệt các lần thử đăng nhập khác nhau
    $key = $this->throttleKey($email, $ip);
    // Check rate limiting: nếu quá 5 lần thử đăng nhập sai thì ném lỗi
    // quá 5 lần thử đăng nhập sai thì ném lỗi
    if (RateLimiter::tooManyAttempts($key, 5)) {
      // Lấy thời gian còn lại trước khi người dùng có thể thử lại và ném lỗi với thông báo tương ứng
      $seconds = RateLimiter::availableIn($key);

      throw new DomainException(
        "Too many login attempts. Try again in {$seconds} seconds.",
        429
      );
    }
    $user = $this->userRepo->findByEmail($email);

    if (!$user || !Hash::check($password, $user->password)) {
      RateLimiter::hit($key, 60); // lock 60s
      throw new DomainException('Invalid credentials', 401);
    }
    // login success → clear attempts
    RateLimiter::clear($key);

    return $user->createToken($deviceName)->plainTextToken;
  }

  public function logout(User $user): void
  {
    $user->currentAccessToken()->delete();
  }

  public function verifyEmail(int $userId, string $hash): void
  {
    $user = $this->userRepo->findById($userId);

    if (!hash_equals(sha1($user->email), $hash)) {
      throw new DomainException('Invalid verification link', 403);
    }

    if ($user->hasVerifiedEmail()) {
      return;
    }

    $user->markEmailAsVerified();
  }
}
