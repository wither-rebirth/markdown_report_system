@extends('layout', ['title' => $title])

@push('meta')
    <meta name="description" content="{{ $excerpt ?? 'Technical report and analysis covering cybersecurity, penetration testing, and vulnerability assessment.' }}">
    <meta name="robots" content="index, follow">
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
    @vite(['resources/js/report-password.js'])
@endpush
@endsection 