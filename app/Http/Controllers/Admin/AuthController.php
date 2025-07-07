<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
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
        
        return view('admin.auth.login');
    }

    /**
     * 处理登录请求
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => '邮箱地址不能为空',
            'email.email' => '邮箱地址格式不正确',
            'password.required' => '密码不能为空',
            'password.min' => '密码至少需要6个字符',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // 尝试登录
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // 检查是否为管理员
            if (!$user->is_admin) {
                Auth::logout();
                return back()->withErrors([
                    'email' => '您没有管理员权限'
                ])->withInput();
            }

            $request->session()->regenerate();
            
            return redirect()->intended(route('admin.dashboard'))->with('success', '登录成功！');
        }

        return back()->withErrors([
            'email' => '邮箱地址或密码不正确'
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

    /**
     * 显示首次设置管理员页面
     */
    public function showSetup()
    {
        // 检查是否已有管理员
        if (User::admins()->exists()) {
            return redirect()->route('admin.login');
        }
        
        return view('admin.auth.setup');
    }

    /**
     * 创建初始管理员账户
     */
    public function setup(Request $request)
    {
        // 检查是否已有管理员
        if (User::admins()->exists()) {
            return redirect()->route('admin.login');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:191|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => '姓名不能为空',
            'name.max' => '姓名最多100个字符',
            'email.required' => '邮箱地址不能为空',
            'email.email' => '邮箱地址格式不正确',
            'email.unique' => '邮箱地址已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码至少需要8个字符',
            'password.confirmed' => '两次输入的密码不一致',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 创建管理员用户
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => true,
        ]);

        // 自动登录
        Auth::login($user);

        return redirect()->route('admin.dashboard')->with('success', '管理员账户创建成功！');
    }
}
