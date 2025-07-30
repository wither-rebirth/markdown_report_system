@extends('layout', ['title' => $title])

@push('meta')
    <meta name="description" content="{{ $excerpt ?? 'Technical report and analysis covering cybersecurity, penetration testing, and vulnerability assessment.' }}">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('styles')
    @vite(['resources/css/report.css', 'resources/css/report-password.css'])
@endpush

@section('content')
<div class="password-container">
    <div class="password-card">
        <div class="password-icon">🔒</div>
        <h1 class="password-title">Protected Report</h1>
        <p class="password-subtitle">
            This report requires a password to access. Please enter the correct password to view the full content.
        </p>
        
        <!-- 显示成功消息 -->
        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
        @endif
        
        <!-- 显示错误消息 -->
        @if(session('error') || $errors->any())
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            @if(session('error'))
                {{ session('error') }}
            @else
                {{ $errors->first() }}
            @endif
            @if(session('csrf_expired'))
                <div class="csrf-help">
                    <small>The page has expired. This usually happens when you stay on the page too long. 
                    <a href="{{ route('reports.show', $slug) }}" onclick="window.location.reload(); return false;">Click here to refresh</a> 
                    or the page will refresh automatically in <span id="countdown">5</span> seconds.</small>
                </div>
            @endif
        </div>
        @endif
        
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
        
        <form action="{{ route('reports.verify-password', $slug) }}" method="POST" class="password-form" id="passwordForm" data-csrf-expired="{{ session('csrf_expired') ? 'true' : 'false' }}">
            @csrf
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <textarea 
                    id="password" 
                    name="password" 
                    class="form-input @error('password') error @enderror"
                    placeholder="Enter password"
                    autocomplete="off"
                    required
                    autofocus
                ></textarea>
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                🔓 Unlock Report
            </button>
        </form>
        
        <a href="{{ route('reports.index') }}" class="back-link">
            ← Back to Report List
        </a>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/report-password.js'])
    <script>
        // Handle form submission and CSRF expired scenarios
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('passwordForm');
            const submitBtn = document.getElementById('submitBtn');
            const countdownElement = document.getElementById('countdown');
            
            // Check if CSRF expired using data attribute
            const csrfExpired = form && form.getAttribute('data-csrf-expired') === 'true';
            
            // Handle CSRF expired auto-refresh
            if (csrfExpired && countdownElement) {
                let countdown = 5;
                const timer = setInterval(function() {
                    countdown--;
                    countdownElement.textContent = countdown;
                    if (countdown <= 0) {
                        clearInterval(timer);
                        window.location.reload();
                    }
                }, 1000);
            }
            
            // Handle form submission
            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    submitBtn.textContent = '⏳ Verifying...';
                    submitBtn.disabled = true;
                });
            }
        });
    </script>
@endpush
@endsection 