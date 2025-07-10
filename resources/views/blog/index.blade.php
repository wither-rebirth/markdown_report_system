@extends('layout', ['title' => request('search') ? '搜索: ' . request('search') . ' | 技术博客 | Wither\'s Blog' : '技术博客 | Wither\'s Blog - 网络安全技术分享平台'])

@push('meta')
    <!-- SEO Meta Tags -->
    @if(request('search'))
        <meta name="description" content="搜索 &quot;{{ request('search') }}&quot; 的相关技术文章，涵盖网络安全、渗透测试、编程技术等专业内容 - Wither's Blog">
        <meta name="keywords" content="{{ request('search') }},技术博客,搜索结果,Wither,网络安全,渗透测试,编程技术">
        <meta name="robots" content="noindex, follow">
    @else
        <meta name="description" content="Wither's Blog 技术博客专区，分享网络安全、渗透测试、编程开发、工具使用等原创技术文章。深入浅出的技术教程，助您提升技术水平。">
        <meta name="keywords" content="技术博客,网络安全,渗透测试,编程开发,CTF,Web安全,系统安全,工具使用,技术教程,Wither">
        <meta name="robots" content="index, follow">
    @endif
    <meta name="author" content="Wither">
    <meta name="revisit-after" content="3 days">
    <link rel="canonical" href="{{ request()->url() }}">
    
    <!-- 分页SEO优化 -->
    @if($posts->hasPages())
        @if($posts->onFirstPage())
            @if($posts->hasMorePages())
                <link rel="next" href="{{ $posts->nextPageUrl() }}">
            @endif
        @else
            <link rel="prev" href="{{ $posts->previousPageUrl() }}">
            @if($posts->hasMorePages())
                <link rel="next" href="{{ $posts->nextPageUrl() }}">
            @endif
        @endif
    @endif
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ request('search') ? '搜索: ' . request('search') . ' | 技术博客' : '技术博客 | Wither\'s Blog' }}">
    @if(request('search'))
        <meta property="og:description" content="搜索 &quot;{{ request('search') }}&quot; 的相关技术文章，涵盖网络安全、渗透测试、编程技术等专业内容">
    @else
        <meta property="og:description" content="Wither's Blog 技术博客专区，分享网络安全、渗透测试、编程开发、工具使用等原创技术文章">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:site_name" content="Wither's Blog">
    <meta property="og:image" content="{{ asset('images/blog-og.jpg') }}">
    <meta property="og:image:alt" content="Wither's Blog 技术博客">
    <meta property="og:locale" content="zh_CN">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ request('search') ? '搜索: ' . request('search') . ' | 技术博客' : '技术博客 | Wither\'s Blog' }}">
    @if(request('search'))
        <meta name="twitter:description" content="搜索 &quot;{{ request('search') }}&quot; 的相关技术文章，涵盖网络安全、渗透测试、编程技术等专业内容">
    @else
        <meta name="twitter:description" content="Wither's Blog 技术博客专区，分享网络安全、渗透测试、编程开发、工具使用等原创技术文章">
    @endif
    <meta name="twitter:image" content="{{ asset('images/blog-og.jpg') }}">
    <meta name="twitter:site" content="@WitherSec">
    <meta name="twitter:creator" content="@WitherSec">
    
    <!-- Additional SEO -->
    <meta name="application-name" content="Wither's Blog">
    <meta name="msapplication-TileColor" content="#3b82f6">
    <meta name="theme-color" content="#3b82f6">
    
    <!-- Structured Data for Blog Section -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Blog",
        "name": "Wither's Blog - 技术博客",
        "description": "专注于网络安全、渗透测试、编程开发等技术领域的原创博客",
        "url": "{{ route('blog.index') }}",
        "publisher": {
            "@type": "Person",
            "name": "Wither",
            "url": "{{ route('aboutme.index') }}"
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ route('blog.index') }}"
        },
        "blogPost": [
            @foreach($posts->take(10) as $post)
            {
                "@type": "BlogPosting",
                "headline": "{{ $post['title'] }}",
                "description": "{{ $post['excerpt'] ?? Str::limit(strip_tags($post['content'] ?? ''), 150) }}",
                "url": "{{ route('blog.show', $post['slug']) }}",
                "datePublished": "{{ date('c', $post['published_at']) }}",
                "dateModified": "{{ date('c', $post['mtime']) }}",
                "author": {
                    "@type": "Person",
                    "name": "{{ $post['author'] }}"
                },
                "publisher": {
                    "@type": "Person",
                    "name": "Wither"
                },
                @if($post['image'])
                "image": "{{ $post['image'] }}",
                @endif
                "articleSection": "{{ $post['category'] }}",
                "keywords": "{{ implode(',', $post['tags'] ?? []) }}",
                "wordCount": {{ $post['reading_time'] * 200 ?? 800 }},
                "timeRequired": "PT{{ $post['reading_time'] ?? 4 }}M"
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
    </script>
    
    @if(!request('search'))
    <!-- Breadcrumb Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "首页",
                "item": "{{ route('home.index') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "技术博客",
                "item": "{{ route('blog.index') }}"
            }
        ]
    }
    </script>
    @endif
@endpush

@push('styles')
    @vite(['resources/css/blog.css'])
@endpush

@section('content')
<div class="blog-index">
    <!-- 面包屑导航 -->
    <nav class="breadcrumb-nav" aria-label="面包屑导航">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home.index') }}" title="返回首页">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z"/>
                    </svg>
                    首页
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                @if(request('search'))
                    搜索结果
                @elseif(request('category'))
                    {{ request('category') }} 分类
                @else
                    技术博客
                @endif
            </li>
        </ol>
    </nav>


    <!-- 搜索栏 -->
    <div class="search-container">
        <form method="GET" action="{{ route('blog.index') }}" class="search-form">
            <div class="search-input-group">
                <div class="search-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       placeholder="搜索文章标题、内容、分类..." 
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
        
        @if(request('search'))
            <div class="search-results-info">
                搜索 "<strong>{{ request('search') }}</strong>" 找到 {{ $posts->total() }} 篇文章
                <a href="{{ route('blog.index') }}" class="clear-search">清除搜索</a>
            </div>
        @endif
    </div>

    <div class="blog-content">
        <!-- 主要内容 -->
        <main class="blog-main">
            @if($posts->count() > 0)
                <div class="posts-grid">
                    @foreach($posts as $post)
                        <article class="post-card" 
                                 data-aos="fade-up" 
                                 data-aos-delay="{{ $loop->index * 100 }}"
                                 itemscope 
                                 itemtype="https://schema.org/BlogPosting">
                            @if($post['image'])
                                <div class="post-image">
                                    <img src="{{ $post['image'] }}" 
                                         alt="{{ $post['title'] }} - {{ $post['category'] }}技术文章配图" 
                                         loading="lazy"
                                         width="350" 
                                         height="200"
                                         decoding="async">
                                </div>
                            @endif
                            <div class="post-content">
                                <div class="post-meta">
                                    <span class="post-category">{{ $post['category'] }}</span>
                                    <span class="post-date">{{ date('Y-m-d', $post['published_at']) }}</span>
                                </div>
                                <h2 class="post-title" itemprop="headline">
                                    <a href="{{ route('blog.show', $post['slug']) }}" itemprop="url">{{ $post['title'] }}</a>
                                </h2>
                                <p class="post-excerpt" itemprop="description">{{ $post['excerpt'] }}</p>
                                <meta itemprop="datePublished" content="{{ date('c', $post['published_at']) }}">
                                <meta itemprop="dateModified" content="{{ date('c', $post['mtime']) }}">
                                <meta itemprop="author" content="{{ $post['author'] }}">
                                <meta itemprop="articleSection" content="{{ $post['category'] }}">
                                <div class="post-footer">
                                    <div class="post-info">
                                        <span class="post-author">{{ $post['author'] }}</span>
                                        <span class="post-reading-time">{{ $post['reading_time'] }} 分钟阅读</span>
                                    </div>
                                    <div class="post-tags">
                                        @foreach(array_slice($post['tags'], 0, 3) as $tag)
                                            <span class="tag">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- 分页 -->
                @if($posts->hasPages())
                    <div class="pagination-wrapper">
                        <nav class="pagination-nav">
                            @if($posts->onFirstPage())
                                <span class="pagination-btn disabled">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                                    </svg>
                                    上一页
                                </span>
                            @else
                                <a href="{{ $posts->previousPageUrl() }}" class="pagination-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                                    </svg>
                                    上一页
                                </a>
                            @endif

                            <div class="pagination-numbers">
                                @foreach($posts->getUrlRange(max(1, $posts->currentPage() - 2), min($posts->lastPage(), $posts->currentPage() + 2)) as $page => $url)
                                    @if($page == $posts->currentPage())
                                        <span class="pagination-number active">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="pagination-number">{{ $page }}</a>
                                    @endif
                                @endforeach
                            </div>

                            @if($posts->hasMorePages())
                                <a href="{{ $posts->nextPageUrl() }}" class="pagination-btn">
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
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10,9 9,9 8,9"/>
                    </svg>
                    <h3>暂无文章</h3>
                    <p>还没有发布任何文章，请稍后再来。</p>
                </div>
            @endif
        </main>

        <!-- 侧边栏 -->
        <aside class="blog-sidebar">


            <!-- 最新文章 -->
            @if(count($latestPosts) > 0)
                <div class="sidebar-widget">
                    <h3 class="widget-title">最新文章</h3>
                    <div class="latest-posts">
                        @foreach($latestPosts as $post)
                            <div class="latest-post">
                                <h4><a href="{{ route('blog.show', $post['slug']) }}">{{ $post['title'] }}</a></h4>
                                <div class="post-meta">
                                    <span class="post-date">{{ date('m-d', $post['published_at']) }}</span>
                                    <span class="post-category">{{ $post['category'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 分类 -->
            @if(count($categories) > 0)
                <div class="sidebar-widget">
                    <h3 class="widget-title">分类</h3>
                    <div class="categories">
                        @foreach($categories as $category)
                            <div class="category-item">
                                <a href="{{ route('blog.index', ['category' => $category['slug']]) }}">
                                    {{ $category['name'] }}
                                </a>
                                <span class="category-count">{{ $category['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 快速链接 -->
            <div class="sidebar-widget">
                <h3 class="widget-title">快速链接</h3>
                <div class="quick-links">
                    <a href="{{ route('reports.index') }}" class="quick-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        靶场报告系统
                    </a>

                    <a href="https://github.com" class="quick-link" target="_blank">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A10,10 0 0,0 2,12C2,16.42 4.87,20.17 8.84,21.5C9.34,21.58 9.5,21.27 9.5,21C9.5,20.77 9.5,20.14 9.5,19.31C6.73,19.91 6.14,17.97 6.14,17.97C5.68,16.81 5.03,16.5 5.03,16.5C4.12,15.88 5.1,15.9 5.1,15.9C6.1,15.97 6.63,16.93 6.63,16.93C7.5,18.45 8.97,18 9.54,17.76C9.63,17.11 9.89,16.67 10.17,16.42C7.95,16.17 5.62,15.31 5.62,11.5C5.62,10.39 6,9.5 6.65,8.79C6.55,8.54 6.2,7.5 6.75,6.15C6.75,6.15 7.59,5.88 9.5,7.17C10.29,6.95 11.15,6.84 12,6.84C12.85,6.84 13.71,6.95 14.5,7.17C16.41,5.88 17.25,6.15 17.25,6.15C17.8,7.5 17.45,8.54 17.35,8.79C18,9.5 18.38,10.39 18.38,11.5C18.38,15.32 16.04,16.16 13.81,16.41C14.17,16.72 14.5,17.33 14.5,18.26C14.5,19.6 14.5,20.68 14.5,21C14.5,21.27 14.66,21.59 15.17,21.5C19.14,20.16 22,16.42 22,12A10,10 0 0,0 12,2Z"/>
                        </svg>
                        GitHub
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function clearSearch() {
    document.querySelector('.search-input').value = '';
    document.querySelector('.search-form').submit();
}

// 搜索快捷键
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.querySelector('.search-input').focus();
    }
});
</script>
@endpush
@endsection 