@extends('layout')

@push('styles')
    @vite(['resources/css/blog.css'])
@endpush

@section('content')
<div class="blog-post">
    <div class="post-container">
        <!-- 返回按钮 -->
        <div class="post-nav">
            <a href="{{ route('blog.index') }}" class="back-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                </svg>
                返回博客
            </a>
        </div>

        <!-- 文章头部 -->
        <header class="post-header">
            @if($post['image'])
                <div class="post-featured-image">
                    <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}">
                </div>
            @endif
            
            <div class="post-meta">
                <div class="post-categories">
                    <span class="post-category">{{ $post['category'] }}</span>
                </div>
                <div class="post-info">
                    <span class="post-author">{{ $post['author'] }}</span>
                    <span class="post-date">{{ date('Y年m月d日', $post['published_at']) }}</span>
                    <span class="post-reading-time">{{ $post['reading_time'] }} 分钟阅读</span>
                </div>
            </div>

            <h1 class="post-title">{{ $post['title'] }}</h1>
            
            @if(count($post['tags']) > 0)
                <div class="post-tags">
                    @foreach($post['tags'] as $tag)
                        <span class="tag">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </header>

        <!-- 文章内容 -->
        <article class="post-content">
            <div class="prose">
                {!! $post['html_content'] !!}
            </div>
        </article>

        <!-- 文章底部 -->
        <footer class="post-footer">
            <div class="post-actions">
                <div class="post-share">
                    <span class="share-label">分享文章:</span>
                    <div class="share-buttons">
                        <a href="#" class="share-btn" onclick="shareToWeibo()" title="分享到微博">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M8.5,8.5A1.5,1.5 0 0,1 10,10A1.5,1.5 0 0,1 8.5,11.5A1.5,1.5 0 0,1 7,10A1.5,1.5 0 0,1 8.5,8.5M15.5,8.5A1.5,1.5 0 0,1 17,10A1.5,1.5 0 0,1 15.5,11.5A1.5,1.5 0 0,1 14,10A1.5,1.5 0 0,1 15.5,8.5M12,17.23C10.25,17.23 8.71,16.5 7.81,15.42L9.23,14C9.68,14.72 10.75,15.17 12,15.17C13.25,15.17 14.32,14.72 14.77,14L16.19,15.42C15.29,16.5 13.75,17.23 12,17.23Z"/>
                            </svg>
                        </a>
                        <a href="#" class="share-btn" onclick="shareToQQ()" title="分享到QQ">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20Z"/>
                            </svg>
                        </a>
                        <a href="#" class="share-btn" onclick="copyLink()" title="复制链接">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3.9,12C3.9,10.29 5.29,8.9 7,8.9H11V7H7A5,5 0 0,0 2,12A5,5 0 0,0 7,17H11V15.1H7C5.29,15.1 3.9,13.71 3.9,12M8,13H16V11H8V13M17,7H13V8.9H17C18.71,8.9 20.1,10.29 20.1,12C20.1,13.71 18.71,15.1 17,15.1H13V17H17A5,5 0 0,0 22,12A5,5 0 0,0 17,7Z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="post-updated">
                    <span class="updated-label">最后更新:</span>
                    <span class="updated-time">{{ date('Y-m-d H:i', $post['mtime']) }}</span>
                </div>
            </div>
        </footer>

        <!-- 相关文章 -->
        @if(count($relatedPosts) > 0)
            <section class="related-posts">
                <h3 class="section-title">相关文章</h3>
                <div class="related-posts-grid">
                    @foreach($relatedPosts as $relatedPost)
                        <a href="{{ route('blog.show', $relatedPost['slug']) }}" class="related-post">
                            <div class="related-post-content">
                                <h4 class="related-post-title">{{ $relatedPost['title'] }}</h4>
                                <p class="related-post-excerpt">{{ $relatedPost['excerpt'] }}</p>
                                <div class="related-post-meta">
                                    <span class="related-post-date">{{ date('m-d', $relatedPost['published_at']) }}</span>
                                    <span class="related-post-category">{{ $relatedPost['category'] }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- 导航链接 -->
        <nav class="post-navigation">
            <a href="{{ route('blog.index') }}" class="nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                </svg>
                所有文章
            </a>
            <a href="{{ route('reports.index') }}" class="nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                </svg>
                靶场报告
            </a>
        </nav>
    </div>
</div>

@push('scripts')
<script>
function shareToWeibo() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    const shareUrl = `https://service.weibo.com/share/share.php?url=${url}&title=${title}`;
    window.open(shareUrl, '_blank', 'width=600,height=400');
}

function shareToQQ() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    const shareUrl = `https://connect.qq.com/widget/shareqq/index.html?url=${url}&title=${title}`;
    window.open(shareUrl, '_blank', 'width=600,height=400');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        // 显示复制成功提示
        const btn = event.target.closest('.share-btn');
        const originalTitle = btn.title;
        btn.title = '链接已复制!';
        setTimeout(() => {
            btn.title = originalTitle;
        }, 2000);
    }).catch(err => {
        console.error('复制失败:', err);
    });
}

// 代码块复制功能
document.addEventListener('DOMContentLoaded', function() {
    const codeBlocks = document.querySelectorAll('pre code');
    codeBlocks.forEach(function(codeBlock) {
        const button = document.createElement('button');
        button.className = 'copy-code-btn';
        button.textContent = '复制';
        button.addEventListener('click', function() {
            navigator.clipboard.writeText(codeBlock.textContent).then(() => {
                button.textContent = '已复制!';
                setTimeout(() => {
                    button.textContent = '复制';
                }, 2000);
            });
        });
        
        const pre = codeBlock.parentElement;
        pre.style.position = 'relative';
        pre.appendChild(button);
    });
});

// 页面滚动时显示返回顶部按钮
window.addEventListener('scroll', function() {
    const scrollButton = document.getElementById('scroll-top-btn');
    if (window.pageYOffset > 300) {
        scrollButton.style.display = 'block';
    } else {
        scrollButton.style.display = 'none';
    }
});
</script>
@endpush
@endsection 