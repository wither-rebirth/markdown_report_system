<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - {{ config('app.name', 'Laravel') }}</title>
    
    @vite(['resources/css/admin/auth.css'])
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <!-- Background animations -->
        <div class="bg-animation">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
        </div>
        
        <div class="login-container">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1>Admin Login</h1>
                <p>Please enter your credentials to access the admin dashboard</p>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('error') }}
                </div>
            @endif
            
            <form action="{{ route('admin.login') }}" method="POST" class="login-form">
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input {{ $errors->has('email') ? 'error' : '' }}"
                            value="{{ old('email') }}"
                            placeholder="Enter your email address"
                            required
                            autocomplete="username"
                        >
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    @if($errors->has('email'))
                        <div class="error-message">
                            <i class="fas fa-times-circle"></i>
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input {{ $errors->has('password') ? 'error' : '' }}"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <i class="fas fa-key input-icon"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                    @if($errors->has('password'))
                        <div class="error-message">
                            <i class="fas fa-times-circle"></i>
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>
                
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="remember" name="remember" class="checkbox-input" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkmark">
                            <i class="fas fa-check"></i>
                        </span>
                        Remember my login
                    </label>
                </div>
                
                <button type="submit" class="login-btn">
                    <span class="btn-text">Login to Dashboard</span>
                    <i class="fas fa-arrow-right btn-icon"></i>
                </button>
            </form>
            
            <div class="security-info">
                <div class="security-item">
                    <i class="fas fa-shield-check"></i>
                    <span>Secure connection enabled</span>
                </div>
                <div class="security-item">
                    <i class="fas fa-clock"></i>
                    <span>Maximum 5 login attempts</span>
                </div>
                <div class="security-item">
                    <i class="fas fa-ban"></i>
                    <span>Locked for 15 minutes after failure</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordEye = document.getElementById('password-eye');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordEye.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordEye.className = 'fas fa-eye';
            }
        }

        // Add floating label effect
        document.querySelectorAll('.form-input').forEach(function(input) {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.parentElement.classList.remove('focused');
                }
            });
            
            // Check if input has value on page load
            if (input.value !== '') {
                input.parentElement.parentElement.classList.add('focused');
            }
        });

        // Add entrance animation
        window.addEventListener('load', function() {
            document.querySelector('.login-container').classList.add('animate-in');
        });
    </script>
</body>
</html> 