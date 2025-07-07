<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>管理员登录 - {{ config('app.name', 'Laravel') }}</title>
    
    @vite(['resources/css/admin/auth.css'])
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 管理员登录</h1>
            <p>请输入您的登录凭据以访问管理后台</p>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif
        
        <form action="{{ route('admin.login') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="email" class="form-label">邮箱地址</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input {{ $errors->has('email') ? 'error' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="请输入邮箱地址"
                    required
                >
                @if($errors->has('email'))
                    <div class="error-message">{{ $errors->first('email') }}</div>
                @endif
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">密码</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input {{ $errors->has('password') ? 'error' : '' }}"
                    placeholder="请输入密码"
                    required
                >
                @if($errors->has('password'))
                    <div class="error-message">{{ $errors->first('password') }}</div>
                @endif
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember" class="checkbox-input" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="checkbox-label">记住我</label>
            </div>
            
            <button type="submit" class="login-btn">
                登录管理后台
            </button>
        </form>
        
        <div class="setup-link">
            <a href="{{ route('admin.setup') }}">首次设置管理员账户</a>
        </div>
    </div>
</body>
</html> 