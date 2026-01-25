<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

// 👉 Repository chịu trách nhiệm tương tác với Model và CSDL → Đây là concrete hay implementation của UserRepositoryInterface
class UserRepository implements UserRepositoryInterface
{
  public function create(array $data): User
  {
    return User::create($data);
  }

  public function findByEmail(string $email): ?User
  {
    return User::where('email', $email)->first();
  }

  public function findById(int $id): User
  {
    return User::findOrFail($id);
  }
}
