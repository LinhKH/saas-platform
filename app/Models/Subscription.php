<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
  protected $fillable = [
    'user_id',
    'plan_id',
    'status',
    'trial_ends_at',
    'current_period_start',
    'current_period_end',
    'cancelled_at',
  ];

  // $casts là gì?
  // Đây là thuộc tính để định nghĩa các trường trong model có kiểu dữ liệu đặc biệt khi được truy xuất hoặc lưu trữ trong cơ sở dữ liệu.
  // Khi bạn định nghĩa một trường trong mảng $casts, Laravel sẽ tự động chuyển đổi kiểu dữ liệu của trường đó khi bạn truy xuất hoặc lưu trữ nó.
  // Ví dụ: nếu bạn định nghĩa một trường là 'datetime', Laravel sẽ tự động chuyển đổi giá trị của trường đó thành đối tượng Carbon khi bạn truy xuất nó từ cơ sở dữ liệu. 
  // Ngược lại, khi bạn lưu trữ một đối tượng Carbon vào trường đó, Laravel sẽ tự động chuyển đổi nó thành định dạng chuỗi phù hợp để lưu trữ trong cơ sở dữ liệu.
  protected $casts = [
    'trial_ends_at' => 'datetime',
    'current_period_start' => 'datetime',
    'current_period_end' => 'datetime',
    'cancelled_at' => 'datetime',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function plan(): BelongsTo
  {
    return $this->belongsTo(Plan::class);
  }

  // đây là gì?
  // Đây là một phương thức trong lớp Subscription, được sử dụng để kiểm tra xem một subscription có đang hoạt động hay không.
  // Phương thức này trả về true nếu subscription có trạng thái 'active' và thời gian kết thúc của kỳ hạn hiện tại (current_period_end) vẫn còn trong tương lai (isFuture()), ngược lại trả về false.
  // tại sao có isFuture?
  // isFuture() là một phương thức của đối tượng Carbon, được sử dụng để kiểm tra xem một thời điểm cụ thể có nằm trong tương lai so với thời điểm hiện tại hay không.
  // Trong trường hợp này, phương thức isActive() sử dụng isFuture() để kiểm tra xem thời gian kết thúc của kỳ hạn hiện tại (current_period_end) của subscription có còn trong tương lai hay không.
  public function isActive(): bool
  {
    return $this->status === 'active' && $this->current_period_end?->isFuture();
  }

  public function isInGracePeriod(int $graceDays = 3): bool
  {
    if ($this->status !== 'past_due') {
      return false;
    }

    return $this->updated_at->addDays($graceDays)->isFuture();
  }
}
