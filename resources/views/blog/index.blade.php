@extends('layout')

@push('styles')
    @vite(['resources/css/blog.css'])
@endpush

@section('content')
<div class="blog-index">
    <!-- 英雄区域 -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">🌟 欢迎来到我的博客</h1>
            <p class="hero-subtitle">分享网络安全、技术学习和日常思考</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ count($posts) }}</span>
                    <span class="stat-label">篇文章</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ count($categories) }}</span>
                    <span class="stat-label">个分类</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ array_sum(array_column($categories, 'count')) }}</span>
                    <span class="stat-label">总阅读</span>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <svg width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="300" height="200" fill="url(#gradient)" rx="10"/>
                <circle cx="80" cy="60" r="25" fill="rgba(255,255,255,0.3)"/>
                <circle cx="220" cy="100" r="15" fill="rgba(255,255,255,0.2)"/>
                <circle cx="150" cy="140" r="20" fill="rgba(255,255,255,0.25)"/>
                <defs>
                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#1e40af;stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
    </section>

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
                        <article class="post-card" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                            @if($post['image'])
                                <div class="post-image">
                                    <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}">
                                </div>
                            @endif
                            <div class="post-content">
                                <div class="post-meta">
                                    <span class="post-category">{{ $post['category'] }}</span>
                                    <span class="post-date">{{ date('Y-m-d', $post['published_at']) }}</span>
                                </div>
                                <h2 class="post-title">
                                    <a href="{{ route('blog.show', $post['slug']) }}">{{ $post['title'] }}</a>
                                </h2>
                                <p class="post-excerpt">{{ $post['excerpt'] }}</p>
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
            <!-- 关于我 -->
            <div class="sidebar-widget">
                <h3 class="widget-title">关于我</h3>
                <div class="about-content">
                    <div class="avatar">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M7.07,18.28C7.5,17.38 10.12,16.5 12,16.5C13.88,16.5 16.5,17.38 16.93,18.28C15.57,19.36 13.86,20 12,20C10.14,20 8.43,19.36 7.07,18.28M18.36,16.83C16.93,15.09 13.46,14.5 12,14.5C10.54,14.5 7.07,15.09 5.64,16.83C4.62,15.5 4,13.82 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,13.82 19.38,15.5 18.36,16.83M12,6C10.06,6 8.5,7.56 8.5,9.5C8.5,11.44 10.06,13 12,13C13.94,13 15.5,11.44 15.5,9.5C15.5,7.56 13.94,6 12,6M12,11A1.5,1.5 0 0,1 10.5,9.5A1.5,1.5 0 0,1 12,8A1.5,1.5 0 0,1 13.5,9.5A1.5,1.5 0 0,1 12,11Z"/>
                        </svg>
                    </div>
                    <p>网络安全爱好者，专注于渗透测试和漏洞挖掘。热爱分享技术知识和学习经验。</p>
                </div>
            </div>

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
                    <a href="{{ route('reports.create') }}" class="quick-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        上传报告
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