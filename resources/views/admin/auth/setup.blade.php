<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>设置管理员账户 - {{ config('app.name', 'Laravel') }}</title>
    
    @vite(['resources/css/admin/auth.css'])
    

</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <div class="setup-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>🚀 初始化管理员</h1>
            <p>欢迎！请创建您的第一个管理员账户来开始使用管理后台。</p>
        </div>
        
        <div class="security-note">
            <h3>🔒 安全提示</h3>
            <ul>
                <li>请使用强密码，至少8个字符</li>
                <li>建议包含大小写字母、数字和特殊字符</li>
                <li>请妥善保管您的登录凭据</li>
                <li>此账户将拥有系统的完全访问权限</li>
            </ul>
        </div>
        
        @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('admin.setup') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="name" class="form-label">管理员姓名</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-input {{ $errors->has('name') ? 'error' : '' }}"
                    value="{{ old('name') }}"
                    placeholder="请输入您的姓名"
                    required
                >
                @if($errors->has('name'))
                    <div class="error-message">{{ $errors->first('name') }}</div>
                @endif
            </div>
            
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
                <div class="form-help">这将作为您的登录用户名</div>
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
                    placeholder="请输入密码（至少8位）"
                    required
                >
                <div class="form-help">建议使用包含大小写字母、数字和符号的强密码</div>
                @if($errors->has('password'))
                    <div class="error-message">{{ $errors->first('password') }}</div>
                @endif
            </div>
            
            <div class="form-group">
                <label for="password_confirmation" class="form-label">确认密码</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    class="form-input"
                    placeholder="请再次输入密码"
                    required
                >
                <div class="form-help">请重复输入上面的密码</div>
            </div>
            
            <button type="submit" class="setup-btn">
                创建管理员账户并登录
            </button>
        </form>
        
        <div class="login-link">
            <a href="{{ route('admin.login') }}">已有账户？直接登录</a>
        </div>
    </div>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html> 