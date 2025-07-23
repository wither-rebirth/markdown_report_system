<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查用户是否已登录
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        // 检查用户是否为管理员
        if (!Auth::user()->isAdmin()) {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'You do not have administrator privileges');
        }

        return $next($request);
    }
}
