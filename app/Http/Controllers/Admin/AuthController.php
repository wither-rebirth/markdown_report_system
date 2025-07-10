<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\LoginAttempt;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    /**
     * 显示登录页面
     */
    public function showLogin()
    {
        // 如果已经登录，重定向到管理端首页
        /** @var User $user */
        $user = Auth::user();
        if (Auth::check() && $user && $user->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        
        // 检查IP是否被锁定
        $ipAddress = request()->ip();
        if (LoginAttempt::isIpLocked($ipAddress, self::MAX_LOGIN_ATTEMPTS, self::LOCKOUT_MINUTES)) {
            $remainingTime = LoginAttempt::getIpLockoutTime($ipAddress, self::MAX_LOGIN_ATTEMPTS, self::LOCKOUT_MINUTES);
            session()->flash('error', "由于多次登录失败，您的IP已被锁定。请等待 {$remainingTime} 分钟后再试。");
        }
        
        return view('admin.auth.login');
    }

    /**
     * 处理登录请求
     */
    public function login(Request $request)
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        
        // 检查IP锁定状态
        if (LoginAttempt::isIpLocked($ipAddress, self::MAX_LOGIN_ATTEMPTS, self::LOCKOUT_MINUTES)) {
            $remainingTime = LoginAttempt::getIpLockoutTime($ipAddress, self::MAX_LOGIN_ATTEMPTS, self::LOCKOUT_MINUTES);
            return back()->withErrors([
                'email' => "由于多次登录失败，您的IP已被锁定。请等待 {$remainingTime} 分钟后再试。"
            ])->withInput();
        }

        // 使用Rate Limiter进行额外保护
        $key = Str::lower($request->ip());
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "请求过于频繁，请等待 {$seconds} 秒后再试。"
            ])->withInput();
        }

        // 验证输入数据
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:191|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'password' => 'required|string|min:6|max:255',
        ], [
            'email.required' => '邮箱地址不能为空',
            'email.email' => '邮箱地址格式不正确',
            'email.max' => '邮箱地址过长',
            'email.regex' => '邮箱地址格式不正确',
            'password.required' => '密码不能为空',
            'password.min' => '密码至少需要6个字符',
            'password.max' => '密码过长',
        ]);

        if ($validator->fails()) {
            RateLimiter::hit($key);
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->input('email');
        
        // 检查邮箱锁定状态
        if (LoginAttempt::isEmailLocked($email, self::MAX_LOGIN_ATTEMPTS, self::LOCKOUT_MINUTES)) {
            LoginAttempt::record($ipAddress, $email, false, $userAgent);
            return back()->withErrors([
                'email' => '该邮箱由于多次登录失败已被锁定，请稍后再试。'
            ])->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // 尝试登录
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // 检查是否为管理员
            if (!$user->is_admin) {
                Auth::logout();
                LoginAttempt::record($ipAddress, $email, false, $userAgent);
                RateLimiter::hit($key);
                return back()->withErrors([
                    'email' => '您没有管理员权限'
                ])->withInput();
            }

            // 记录成功的登录尝试
            LoginAttempt::record($ipAddress, $email, true, $userAgent);
            
            // 清除Rate Limiter
            RateLimiter::clear($key);
            
            $request->session()->regenerate();
            
            return redirect()->intended(route('admin.dashboard'))->with('success', '登录成功！');
        }

        // 记录失败的登录尝试
        LoginAttempt::record($ipAddress, $email, false, $userAgent);
        RateLimiter::hit($key);

        // 获取剩余尝试次数
        $failedAttempts = LoginAttempt::getFailedAttempts($ipAddress, self::LOCKOUT_MINUTES);
        $remainingAttempts = self::MAX_LOGIN_ATTEMPTS - $failedAttempts;

        $errorMessage = '邮箱地址或密码不正确';
        if ($remainingAttempts <= 2 && $remainingAttempts > 0) {
            $errorMessage .= "，您还有 {$remainingAttempts} 次尝试机会";
        }

        return back()->withErrors([
            'email' => $errorMessage
        ])->withInput();
    }

    /**
     * 登出
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login')->with('success', '已成功退出登录');
    }
}
