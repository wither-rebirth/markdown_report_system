@extends('layout', ['title' => $full_title, 'hasCanonical' => true])

@push('meta')
    <!-- SEO Meta Tags -->
    @if($excerpt)
    <meta name="description" content="{{ $excerpt }}">
    @endif
    <meta name="keywords" content="{{ $keywords ?? 'Wither,Security Research,Penetration Testing,Tech Sharing' }}">
    <meta name="author" content="Wither">
    <meta name="robots" content="index, follow">
    <meta name="revisit-after" content="7 days">
    <link rel="canonical" href="{{ $canonical_url ?? request()->url() }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $full_title ?? $title }}">
    @if($excerpt)
    <meta property="og:description" content="{{ $excerpt }}">
    @endif
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $canonical_url ?? request()->url() }}">
    <meta property="og:site_name" content="Wither's Blog">
    <meta property="og:locale" content="en_US">
    @if(($type ?? '') === 'hackthebox')
        <meta property="og:image" content="{{ asset('images/hackthebox-og.jpg') }}">
        <meta property="og:image:alt" content="HackTheBox Writeup - {{ $title }}">
    @else
        <meta property="og:image" content="{{ asset('images/wither-og.jpg') }}">
        <meta property="og:image:alt" content="Wither's Blog - {{ $title }}">
    @endif
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $full_title ?? $title }}">
    @if($excerpt)
    <meta name="twitter:description" content="{{ $excerpt }}">
    @endif
    @if(($type ?? '') === 'hackthebox')
        <meta name="twitter:image" content="{{ asset('images/hackthebox-og.jpg') }}">
    @else
        <meta name="twitter:image" content="{{ asset('images/wither-og.jpg') }}">
    @endif
    
    <!-- Additional SEO -->
    <meta name="article:author" content="Wither">
    <meta name="article:published_time" content="{{ date('c', $mtime) }}">
    <meta name="article:modified_time" content="{{ date('c', $mtime) }}">
    @if(($type ?? '') === 'hackthebox')
        <meta name="article:section" content="HackTheBox">
        <meta name="article:tag" content="HackTheBox,Writeup,CTF,Penetration Testing">
    @else
        <meta name="article:section" content="Security Research">
        <meta name="article:tag" content="Security Research,Penetration Testing,Cybersecurity">
    @endif
@endpush

@push('styles')
    @vite(['resources/css/report.css'])
@endpush

@section('content')
<div class="report-page">
    <!-- 结构化数据 -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "{{ $title }}",
        @if($excerpt)
        "description": "{{ $excerpt }}",
        @endif
        "author": {
            "@type": "Person",
            "name": "Wither",
            "url": "{{ route('aboutme.index') }}"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Wither's Blog",
            "url": "{{ route('home.index') }}"
        },
        "datePublished": "{{ date('c', $mtime) }}",
        "dateModified": "{{ date('c', $mtime) }}",
        "url": "{{ $canonical_url ?? request()->url() }}",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ $canonical_url ?? request()->url() }}"
        },
        @if(($type ?? '') === 'hackthebox')
        "genre": "HackTheBox Writeup",
        "keywords": "{{ $keywords ?? 'HackTheBox,Writeup,CTF,Penetration Testing' }}",
        "about": {
            "@type": "Thing",
            "name": "HackTheBox",
            "description": "Penetration Testing Labs and CTF Challenges"
        },
        @else
        "genre": "Security Research",
        "keywords": "{{ $keywords ?? 'Security Research,Penetration Testing,Cybersecurity' }}",
        "about": {
            "@type": "Thing",
            "name": "Cybersecurity",
            "description": "Information Security and Penetration Testing"
        },
        @endif
        "proficiencyLevel": "Intermediate",
        "dependencies": "Basic knowledge of penetration testing"
    }
    </script>

    <!-- 报告布局容器 -->
    <div class="report-layout">
        <!-- 侧边栏目录 -->
        <aside class="report-sidebar no-print">
            <div class="sidebar-content">
                <div class="sidebar-header">
                    <h3>📋 Table of Contents</h3>
                    <button class="toc-toggle" onclick="toggleTocSidebar()" title="Collapse/Expand Table of Contents">
                        <span class="toggle-icon">◀</span>
                    </button>
                </div>
                <div id="table-of-contents" class="toc-container"></div>
            </div>
        </aside>

        <!-- 主要内容区域 -->
        <main class="report-main">
            <!-- 报告头部信息 -->
            <div class="report-header">
                <div class="header-top">
                    <h1 class="report-title">{{ $title }}</h1>
                    <div class="header-actions no-print">
                        <button class="action-btn" onclick="toggleTocSidebar()" title="Show/Hide Table of Contents">📋</button>
                        <button class="action-btn" onclick="window.print()" title="Print Report">🖨️</button>
                        <button class="action-btn" onclick="toggleFullscreen()" title="Fullscreen Mode">🔍</button>
                    </div>
                </div>
                
                <div class="report-meta">
                    📅 Last Updated: {{ date('M d, Y H:i', $mtime) }} | 
                    📄 Size: {{ number_format($size / 1024, 1) }} KB | 
                    @if(($type ?? '') === 'hackthebox')
                        🎯 Type: HackTheBox Writeup
                        @if(isset($difficulty))
                            | 🎚️ Difficulty: <span class="difficulty-tag difficulty-{{ strtolower($difficulty) }}">{{ $difficulty }}</span>
                        @endif
                        |
                    @else
                        🎯 Type: Security Research Report |
                    @endif
                    🔗 <a href="{{ route('reports.categories') }}">Back to Categories</a>
                </div>
            </div>

            <!-- 报告内容 -->
            <article class="report-content">
                {!! $html !!}
            </article>
        </main>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/report.js'])
@endpush
@endsection