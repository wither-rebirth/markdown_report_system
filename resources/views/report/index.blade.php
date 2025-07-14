@extends('layout', ['title' => '靶场报告', 'hasCanonical' => true])

@push('meta')
    <!-- SEO Meta Tags -->
    <meta name="description" content="Wither's Blog 靶场报告专区，包含 HackTheBox、TryHackMe 等各类渗透测试靶场的详细 Writeup 和解题思路分享。">
    <meta name="keywords" content="HackTheBox,TryHackMe,CTF,Writeup,Walkthrough,靶场报告,渗透测试,网络安全,Wither,技术分享">
    <meta name="author" content="Wither">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('reports.index') }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="靶场报告 | Wither's Blog">
    <meta property="og:description" content="Wither's Blog 靶场报告专区，包含 HackTheBox、TryHackMe 等各类渗透测试靶场的详细 Writeup 和解题思路分享。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('reports.index') }}">
    <meta property="og:site_name" content="Wither's Blog">
    <meta property="og:image" content="{{ asset('images/reports-og.jpg') }}">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="靶场报告 | Wither's Blog">
    <meta name="twitter:description" content="Wither's Blog 靶场报告专区，包含 HackTheBox、TryHackMe 等各类渗透测试靶场的详细 Writeup 和解题思路分享。">
    <meta name="twitter:image" content="{{ asset('images/reports-og.jpg') }}">
@endpush

@push('styles')
    @vite(['resources/css/index.css'])
@endpush

@section('content')
<div class="report-index">
    <!-- 页面头部 -->
    <div class="page-header">
        <div class="page-info">
            <h2>📊 报告列表</h2>
            @if($reports->total() > 0)
            <p class="total-info">
                @if(request('search'))
                    搜索 "{{ request('search') }}" 找到 <strong>{{ $reports->total() }}</strong> 个报告
                @else
                    共 <strong>{{ $reports->total() }}</strong> 个报告
                @endif
                @if($reports->hasPages())
                    ，当前第 <strong>{{ $reports->currentPage() }}</strong> 页，
                    共 <strong>{{ $reports->lastPage() }}</strong> 页
                    (每页显示 {{ $reports->perPage() }} 个)
                @endif
            </p>
            @endif
        </div>
    </div>
    
    <!-- 搜索栏 -->
    <div class="search-container">
        <form method="GET" action="{{ route('reports.index') }}" class="search-form">
            <div class="search-input-group">
                <div class="search-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       id="report-search" 
                       placeholder="搜索报告标题、内容..." 
                       value="{{ request('search') }}"
                       class="search-input"
                       autocomplete="off">
                @if(request('search'))
                    <button type="button" class="search-clear" onclick="clearSearch()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                        </svg>
                    </button>
                @endif
            </div>
            <button type="submit" class="search-btn">搜索</button>
        </form>
        
        <!-- 搜索提示 -->
        <div class="search-tips">
            <span class="search-tip">💡 小贴士：按 <kbd>Ctrl</kbd> + <kbd>K</kbd> 快速聚焦搜索框</span>
            @if(request('search'))
                <a href="{{ route('reports.index') }}" class="clear-search-link">清除搜索</a>
            @endif
        </div>
    </div>

    @if(count($reports) > 0)
    <!-- 报告列表 -->
    <div class="report-list">
        @foreach ($reports as $report)
        <a href="{{ route('reports.show', $report['slug']) }}" class="report-card-link">
            <div class="report-card" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <!-- 卡片左侧：图标和内容 -->
                <div class="card-main-content">
                    <div class="card-icon">
                        @if(isset($report['type']) && $report['type'] === 'hackthebox')
                            <!-- Hackthebox 图标 -->
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.996 2l7.071 4.001v8l-7.071 4.001-7.071-4.001v-8l7.071-4.001zm0 1.5l-5.596 3.167v6.666l5.596 3.167 5.596-3.167v-6.666l-5.596-3.167zm0 2.5c1.519 0 2.75 1.231 2.75 2.75s-1.231 2.75-2.75 2.75-2.75-1.231-2.75-2.75 1.231-2.75 2.75-2.75zm0 1.5c-.69 0-1.25.56-1.25 1.25s.56 1.25 1.25 1.25 1.25-.56 1.25-1.25-.56-1.25-1.25-1.25z"/>
                            </svg>
                        @else
                            @switch(pathinfo($report['slug'], PATHINFO_EXTENSION))
                                @case('sql')
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M5,3H19A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3M5,5V19H19V5H5M7,7H17V9H7V7M7,11H17V13H7V11M7,15H17V17H7V15Z"/>
                                    </svg>
                                    @break
                                @case('xss')
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V15H11V17M11,13H13V7H11V13Z"/>
                                    </svg>
                                    @break
                                @default
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                    </svg>
                            @endswitch
                        @endif
                    </div>
                    
                    <div class="card-content">
                        <h3 class="report-title">{{ $report['title'] }}</h3>
                        <p class="report-excerpt">{{ $report['excerpt'] ?? '点击查看完整内容...' }}</p>
                        
                        <div class="report-meta">
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M16.2,16.2L11,13V7H12.5V12.2L17,14.9L16.2,16.2Z"/>
                                </svg>
                                {{ date('Y-m-d H:i', $report['mtime']) }}
                            </span>
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                                {{ number_format($report['size'] / 1024, 1) }} KB
                            </span>
                            @if(isset($report['type']) && $report['type'] === 'hackthebox' && isset($report['image_count']) && $report['image_count'] > 0)
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8.5,13.5L11,16.5L14.5,12L19,18H5M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19Z"/>
                                </svg>
                                {{ $report['image_count'] }} 张图片
                            </span>
                            @endif
                            @if(isset($report['type']) && $report['type'] === 'hackthebox')
                            <span class="meta-item hackthebox-badge">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.996 2l7.071 4.001v8l-7.071 4.001-7.071-4.001v-8l7.071-4.001z"/>
                                </svg>
                                HTB
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- 卡片右侧：箭头指示器 -->
                <div class="card-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                    </svg>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    
    <!-- 分页导航 -->
    @if($reports->hasPages())
    <div class="pagination-wrapper">
        <nav class="pagination-nav">
            <!-- 上一页 -->
            @if($reports->onFirstPage())
                <span class="pagination-btn disabled">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                    </svg>
                    上一页
                </span>
            @else
                <a href="{{ $reports->previousPageUrl() }}" class="pagination-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                    </svg>
                    上一页
                </a>
            @endif
            
            <!-- 页码 -->
            <div class="pagination-numbers">
                @foreach($reports->getUrlRange(max(1, $reports->currentPage() - 2), min($reports->lastPage(), $reports->currentPage() + 2)) as $page => $url)
                    @if($page == $reports->currentPage())
                        <span class="pagination-number active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-number">{{ $page }}</a>
                    @endif
                @endforeach
            </div>
            
            <!-- 下一页 -->
            @if($reports->hasMorePages())
                <a href="{{ $reports->nextPageUrl() }}" class="pagination-btn">
                    下一页
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                    </svg>
                </a>
            @else
                <span class="pagination-btn disabled">
                    下一页
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                    </svg>
                </span>
            @endif
        </nav>
        
        <!-- 快速跳转 -->
        <div class="pagination-jump">
            <span>跳转到</span>
            <input type="number" id="jumpToPage" min="1" max="{{ $reports->lastPage() }}" value="{{ $reports->currentPage() }}" class="page-input">
            <button onclick="jumpToPage()" class="jump-btn">确定</button>
        </div>
    </div>
    @endif
    @else
    <!-- 空状态 -->
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
            </svg>
        </div>
        <h3>📭 暂无报告</h3>
        <p>暂时没有可用的报告。</p>
    </div>
    @endif
</div>



@push('scripts')
    @vite(['resources/js/index.js'])
@endpush
@endsection
