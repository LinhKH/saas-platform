<?php

namespace App\Repositories\Contracts;

use App\Models\User;

// 👉 Đây là contract (interface) cho UserRepository
//   định nghĩa các phương thức mà UserRepository phải implement
//  giúp tách rời phần định nghĩa và phần triển khai
//  giúp dễ dàng thay đổi implementation sau này
//  ví dụ thay Eloquent bằng raw SQL hoặc một ORM khác
//  mà không ảnh hưởng đến phần còn lại của ứng dụng
//  chỉ cần viết một class mới implement interface này
//  và bind nó trong RepositoryServiceProvider
//  tóm lại, UserRepositoryInterface đóng vai trò quan trọng trong việc xây dựng một kiến trúc phần mềm sạch, linh hoạt và dễ bảo trì.
interface UserRepositoryInterface
{
  public function create(array $data): User;

  public function findByEmail(string $email): ?User;

  public function findById(int $id): User;
}
