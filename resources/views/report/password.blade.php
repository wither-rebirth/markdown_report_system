@extends('layout', ['title' => 'Enter Password - ' . $title])

@push('meta')
    <meta name="description" content="This report requires password access. Please enter the correct password to view the content.">
    <meta name="robots" content="noindex, nofollow">
@endpush

@push('styles')
    @vite(['resources/css/report.css'])
    <style>
        .password-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            padding: 2rem;
        }
        
        .password-card {
            background: var(--background-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 3rem 2.5rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .password-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .password-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .password-subtitle {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .password-form {
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--background-primary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        
        .form-error {
            color: #e74c3c;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }
        
        .submit-btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .submit-btn:hover {
            background: var(--primary-hover);
        }
        
        .submit-btn:disabled {
            background: var(--text-secondary);
            cursor: not-allowed;
        }
        
        .back-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: var(--primary-color);
        }
        
        .report-info {
            background: var(--background-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .report-info h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            color: var(--text-primary);
        }
        
        .report-meta {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .htb-notice {
            display: flex;
            align-items: flex-start;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .htb-notice .notice-icon {
            font-size: 1.2rem;
            margin-right: 0.75rem;
            margin-top: 0.1rem;
            flex-shrink: 0;
        }
        
        .htb-notice .notice-content {
            flex: 1;
        }
        
        .htb-notice .notice-content strong {
            display: block;
            color: #856404;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .htb-notice .notice-content p {
            color: #856404;
            font-size: 0.85rem;
            line-height: 1.4;
            margin: 0;
        }
        
        /* Dark mode support for HTB notice */
        @media (prefers-color-scheme: dark) {
            .htb-notice {
                background: rgba(255, 243, 205, 0.1);
                border-color: rgba(255, 234, 167, 0.3);
            }
            
            .htb-notice .notice-content strong,
            .htb-notice .notice-content p {
                color: #f1c40f;
            }
        }
        
        @media (max-width: 768px) {
            .password-container {
                min-height: 50vh;
                padding: 1rem;
            }
            
            .password-card {
                padding: 2rem 1.5rem;
            }
            
            .password-icon {
                font-size: 2.5rem;
            }
            
            .password-title {
                font-size: 1.25rem;
            }
        }
    </style>
@endpush

@section('content')
<div class="password-container">
    <div class="password-card">
        <div class="password-icon">🔒</div>
        <h1 class="password-title">Protected Report</h1>
        <p class="password-subtitle">
            This report requires a password to access. Please enter the correct password to view the full content.
        </p>
        
        <div class="report-info">
            <h3>📄 {{ $title }}</h3>
            <div class="report-meta">
                📅 Last Updated: {{ date('M d, Y H:i', $mtime) }}
            </div>
        </div>
        
        @if(str_starts_with($slug, 'htb-'))
        <div class="htb-notice">
            <div class="notice-icon">⚠️</div>
            <div class="notice-content">
                <strong>HTB Official Policy Notice</strong>
                <p>Due to HackTheBox official regulations, machine solutions cannot be disclosed publicly until the machine is officially retired. This content is password-protected to comply with HTB's responsible disclosure policy.</p>
            </div>
        </div>
        @endif
        
        <form action="{{ route('reports.verify-password', $slug) }}" method="POST" class="password-form">
            @csrf
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input @error('password') error @enderror"
                    placeholder="Enter password"
                    autocomplete="off"
                    required
                    autofocus
                >
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
            
            <button type="submit" class="submit-btn">
                🔓 Unlock Report
            </button>
        </form>
        
        <a href="{{ route('reports.index') }}" class="back-link">
            ← Back to Report List
        </a>
    </div>
</div>

@push('scripts')
    <script>
        // Auto-focus password field on page load
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.focus();
            }
        });
        
        // Show/hide password toggle
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }
    </script>
@endpush
@endsection 