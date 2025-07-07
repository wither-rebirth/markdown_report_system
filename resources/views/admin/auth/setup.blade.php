<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>设置管理员账户 - {{ config('app.name', 'Laravel') }}</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .setup-container {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 500px;
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .setup-header h1 {
            color: #1f2937;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .setup-header p {
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        .setup-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            color: #374151;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-input.error {
            border-color: #ef4444;
        }
        
        .form-help {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        .setup-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.15s ease-in-out;
            margin-top: 1rem;
        }
        
        .setup-btn:hover {
            transform: translateY(-1px);
        }
        
        .setup-btn:active {
            transform: translateY(0);
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .alert {
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .security-note {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .security-note h3 {
            color: #0369a1;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .security-note ul {
            color: #0369a1;
            font-size: 0.75rem;
            margin-left: 1rem;
        }
        
        .security-note li {
            margin-bottom: 0.25rem;
        }
    </style>
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