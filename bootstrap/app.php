<?php

use App\Exceptions\DomainException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware): void {
    // Sanctum API token-based, không session, không Blade.  
    $middleware->statefulApi();
  })
  ->withExceptions(function (Exceptions $exceptions): void {
    /**
     👉 Từ giờ:

    ❌ Không try/catch trong controller

    ❌ Không trả JSON lung tung

    ✅ API clean & predictable
     */
    $exceptions->render(function (DomainException $e, Request $request) {
      return response()->json([
        'message' => $e->getMessage(),
      ], $e->getCode());
    });
  })->create();
