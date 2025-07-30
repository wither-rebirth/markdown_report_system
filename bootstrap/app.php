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
        // 处理419 CSRF Token错误
        $exceptions->render(function (Illuminate\Session\TokenMismatchException $e, $request) {
            // 如果是AJAX请求，返回JSON响应
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'CSRF token mismatch. Please refresh the page and try again.',
                    'csrf_token' => csrf_token()
                ], 419);
            }
            
            // 如果是表单提交，重定向回来并显示友好错误消息
            if ($request->isMethod('post')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Page expired due to inactivity. Please try again.')
                    ->with('csrf_expired', true);
            }
            
            // 其他情况重定向到首页
            return redirect()->route('home.index')
                ->with('error', 'Session expired. Please try again.');
        });
    })->create(); 