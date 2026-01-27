<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail // khi implements MustVerifyEmail Laravel tự dùng column email_verified_at.
{
  use HasApiTokens, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function subscription()
  {
    /**
🧠 Vì sao dùng latestOfMany()?

User có thể có nhiều subscription trong lịch sử

Nhưng tại 1 thời điểm chỉ có 1 subscription hiện hành

latestOfMany() = lấy bản ghi mới nhất

👉 Đây là cách Laravel khuyên dùng cho SaaS
     */
    return $this->hasOne(Subscription::class)->latestOfMany();
  }
}
