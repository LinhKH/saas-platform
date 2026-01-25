<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;


// 👉 Sau này toàn bộ API dùng chung format
abstract class BaseApiController extends Controller
{
    protected function success($data = null, string $message = 'OK')
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ]);
    }
}
