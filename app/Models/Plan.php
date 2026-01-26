<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
  protected $fillable = [
    'code',
    'name',
    'price',
    'interval',
    'trial_days',
    'active',
  ];
  // giải thích về $casts
  // thuộc tính $casts trong model Plan được sử dụng để chỉ định cách các thuộc tính của model nên được chuyển đổi khi truy xuất hoặc lưu trữ trong cơ sở dữ liệu.
  // Trong trường hợp này, 'price' được chuyển đổi thành kiểu decimal với 2 chữ số thập phân, và 'active' được chuyển đổi thành kiểu boolean.
  // tại sao phải làm vậy trong khi trong migration đã định nghĩa kiểu dữ liệu rồi?
  // việc sử dụng $casts giúp đảm bảo rằng khi bạn truy xuất các thuộc tính này từ model, chúng sẽ luôn có kiểu dữ liệu đúng như mong đợi, 
  // bất kể cách chúng được lưu trữ trong cơ sở dữ liệu. Điều này đặc biệt hữu ích khi làm việc với các kiểu dữ liệu phức tạp như decimal và boolean,
  // giúp tránh các lỗi không mong muốn khi xử lý dữ liệu trong ứng dụng của bạn.
  // Ví dụ: khi bạn truy xuất thuộc tính 'price' từ một instance của Plan, nó sẽ luôn được trả về dưới dạng một số thập phân với 2 chữ số thập phân,
  // và khi bạn truy xuất thuộc tính 'active', nó sẽ luôn được trả về dưới dạng boolean (true hoặc false).
  protected $casts = [
    'price' => 'decimal:2',
    'active' => 'boolean',
  ];
}
