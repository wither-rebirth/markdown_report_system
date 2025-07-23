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
            session()->flash('error', "Your IP has been locked due to multiple failed login attempts. Please wait {$remainingTime} minutes before trying again.");
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
                'email' => "Your IP has been locked due to multiple failed login attempts. Please wait {$remainingTime} minutes before trying again."
            ])->withInput();
        }

        // 使用Rate Limiter进行额外保护
        $key = Str::lower($request->ip());
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many requests. Please wait {$seconds} seconds before trying again."
            ])->withInput();
        }

        // 验证输入数据
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:191|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'password' => 'required|string|min:6|max:255',
        ], [
            'email.required' => 'Email address is required',
            'email.email' => 'Invalid email address format',
            'email.max' => 'Email address is too long',
            'email.regex' => 'Invalid email address format',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
            'password.max' => 'Password is too long',
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
                'email' => 'This email has been locked due to multiple failed login attempts. Please try again later.'
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
                    'email' => 'You do not have administrator privileges'
                ])->withInput();
            }

            // 记录成功的登录尝试
            LoginAttempt::record($ipAddress, $email, true, $userAgent);
            
            // 清除Rate Limiter
            RateLimiter::clear($key);
            
            $request->session()->regenerate();
            
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Login successful!');
        }

        // 记录失败的登录尝试
        LoginAttempt::record($ipAddress, $email, false, $userAgent);
        RateLimiter::hit($key);

        // 获取剩余尝试次数
        $failedAttempts = LoginAttempt::getFailedAttempts($ipAddress, self::LOCKOUT_MINUTES);
        $remainingAttempts = self::MAX_LOGIN_ATTEMPTS - $failedAttempts;

        $errorMessage = 'Invalid email address or password';
        if ($remainingAttempts <= 2 && $remainingAttempts > 0) {
            $errorMessage .= ". You have {$remainingAttempts} attempts remaining";
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
        
        return redirect()->route('admin.login')->with('success', 'Successfully logged out');
    }
}
