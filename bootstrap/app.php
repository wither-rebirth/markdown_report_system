<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminAuth::class,
        ]);
        
        // 全局中间件 - 访问统计
        $middleware->web(append: [
            \App\Http\Middleware\TrackPageViews::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create(); 