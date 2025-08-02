@extends('layout', ['title' => 'Lab Reports Categories', 'hasCanonical' => true])

@push('meta')
    <!-- SEO Meta Tags -->
    <meta name="description" content="Wither's Blog Lab Reports Categories - Browse HackTheBox, TryHackMe, VulnHub reports organized by platform and difficulty.">
    <meta name="keywords" content="HackTheBox,TryHackMe,VulnHub,CTF,Writeup,Walkthrough,Lab Reports,Penetration Testing,Cybersecurity,Wither">
    <meta name="author" content="Wither">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('reports.categories') }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Lab Reports Categories | Wither's Blog">
    <meta property="og:description" content="Browse lab reports by platform: HackTheBox, TryHackMe, VulnHub and more.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('reports.categories') }}">
    <meta property="og:site_name" content="Wither's Blog">
    <meta property="og:image" content="{{ asset('images/reports-og.jpg') }}">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Lab Reports Categories | Wither's Blog">
    <meta name="twitter:description" content="Browse lab reports by platform: HackTheBox, TryHackMe, VulnHub and more.">
    <meta name="twitter:image" content="{{ asset('images/reports-og.jpg') }}">
@endpush

@push('styles')
    @vite(['resources/css/index.css'])
@endpush

@section('content')
<div class="report-categories">
    <!-- 页面头部 -->
    <div class="page-header">
        <div class="page-info">
            <h2>📊 Lab Reports</h2>
            <p class="page-description">
                Choose a platform to explore detailed writeups and walkthroughs
            </p>
        </div>
    </div>

    <!-- 分类网格 -->
    <div class="categories-grid">
        @forelse($categories as $category)
        <a href="{{ route('reports.index', $category['key']) }}" class="category-card-link">
            <div class="category-card" data-aos="fade-up" data-aos-delay="{{ min($loop->index * 50, 300) }}">
                <!-- 卡片图标 -->
                <div class="category-icon">
                    @switch($category['icon'])
                        @case('htb-machines')
                            <img src="{{ asset('images/machines.png') }}" alt="HackTheBox Machines" class="category-image">
                            @break
                        @case('htb-fortresses')
                            <img src="{{ asset('images/fortresses.png') }}" alt="HackTheBox Fortresses" class="category-image">
                            @break
                        @case('htb-endgames')
                            <img src="{{ asset('images/insane.png') }}" alt="HackTheBox EndGames" class="category-image">
                            @break
                        @case('htb-insane')
                            <img src="{{ asset('images/insane.png') }}" alt="HackTheBox Insane" class="category-image">
                            @break
                        @case('tryhackme')
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2C13.1,2 14,2.9 14,4C14,5.1 13.1,6 12,6C10.9,6 10,5.1 10,4C10,2.9 10.9,2 12,2M21,9V7L15,1H9C7.9,1 7,1.9 7,3V7H9V3H13V9H21Z"/>
                            </svg>
                            @break
                        @case('vulnhub')
                            <img src="{{ asset('images/vulnhub.png') }}" alt="VulnHub Machines" class="category-image">
                            @break
                        @default
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                            </svg>
                    @endswitch
                </div>
                
                <!-- 卡片内容 -->
                <div class="category-content">
                    <h3 class="category-title">{{ $category['title'] }}</h3>
                    <p class="category-description">{{ $category['description'] }}</p>
                    
                    <div class="category-stats">
                        <span class="reports-count">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                            </svg>
                            {{ $category['count'] }} reports
                        </span>
                    </div>
                </div>
                
                <!-- 箭头 -->
                <div class="category-arrow">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                    </svg>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state">
            <div class="empty-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6,2C4.89,2 4,2.89 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6Z"/>
                </svg>
            </div>
            <h3>No Report Categories Found</h3>
            <p>There are no report categories available at the moment.</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
    @vite(['resources/js/index.js'])
@endpush
@endsection