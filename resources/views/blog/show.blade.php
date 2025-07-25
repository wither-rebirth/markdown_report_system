@extends('layout', ['title' => $post['title'] . ' | Wither\'s Blog', 'hasCanonical' => true])

@push('meta')
    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $post['excerpt'] ?? Str::limit(strip_tags($post['content'] ?? ''), 155) }}">
    <meta name="keywords" content="{{ implode(',', $post['tags'] ?? []) }},{{ $post['category'] }},Tech Blog,Wither,Cybersecurity,Programming Technology">
    <meta name="author" content="{{ $post['author'] }}">
    <meta name="robots" content="index, follow">
    <meta name="revisit-after" content="7 days">
    <link rel="canonical" href="{{ route('blog.show', $post['slug']) }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $post['title'] }}">
    <meta property="og:description" content="{{ $post['excerpt'] ?? Str::limit(strip_tags($post['content'] ?? ''), 155) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('blog.show', $post['slug']) }}">
    <meta property="og:site_name" content="Wither's Blog">
    @if($post['image'])
        <meta property="og:image" content="{{ $post['image'] }}">
        <meta property="og:image:alt" content="{{ $post['title'] }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @else
        <meta property="og:image" content="{{ asset('images/blog-og.jpg') }}">
        <meta property="og:image:alt" content="Wither's Blog - {{ $post['title'] }}">
    @endif
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $post['title'] }}">
    <meta name="twitter:description" content="{{ $post['excerpt'] ?? Str::limit(strip_tags($post['content'] ?? ''), 155) }}">
    @if($post['image'])
        <meta name="twitter:image" content="{{ $post['image'] }}">
    @else
        <meta name="twitter:image" content="{{ asset('images/blog-og.jpg') }}">
    @endif
    <meta name="twitter:site" content="@WitherSec">
    <meta name="twitter:creator" content="@WitherSec">
    
    <!-- Article Meta Tags -->
    <meta name="article:author" content="{{ $post['author'] }}">
    <meta name="article:published_time" content="{{ date('c', $post['published_at']) }}">
    <meta name="article:modified_time" content="{{ date('c', $post['mtime']) }}">
    <meta name="article:section" content="{{ $post['category'] }}">
    @foreach($post['tags'] ?? [] as $tag)
        <meta name="article:tag" content="{{ $tag }}">
    @endforeach
    
    <!-- Structured Data for Blog Post -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "{{ $post['title'] }}",
        "description": "{{ $post['excerpt'] ?? Str::limit(strip_tags($post['content'] ?? ''), 150) }}",
        "author": {
            "@type": "Person",
            "name": "{{ $post['author'] }}",
            "url": "{{ route('aboutme.index') }}"
        },
        "publisher": {
            "@type": "Person",
            "name": "Wither",
            "url": "{{ route('aboutme.index') }}"
        },
        "datePublished": "{{ date('c', $post['published_at']) }}",
        "dateModified": "{{ date('c', $post['mtime']) }}",
        "url": "{{ route('blog.show', $post['slug']) }}",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ route('blog.show', $post['slug']) }}"
        },
        @if($post['image'])
        "image": {
            "@type": "ImageObject",
            "url": "{{ $post['image'] }}",
            "width": 1200,
            "height": 630,
            "caption": "{{ $post['title'] }}"
        },
        @endif
        "articleSection": "{{ $post['category'] }}",
        "keywords": "{{ implode(',', $post['tags'] ?? []) }}",
        "wordCount": {{ $post['reading_time'] * 200 ?? 800 }},
        "timeRequired": "PT{{ $post['reading_time'] ?? 4 }}M",
        "inLanguage": "en-US",
        "isPartOf": {
            "@type": "Blog",
            "name": "Wither's Blog",
            "url": "{{ route('blog.index') }}"
        },
        "about": {
            "@type": "Thing",
            "name": "{{ $post['category'] }}",
            "description": "{{ $post['category'] }}-related technical content"
        }
    }
    </script>
    
    <!-- Breadcrumb Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": "{{ route('home.index') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Tech Blog",
                "item": "{{ route('blog.index') }}"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "{{ $post['title'] }}",
                "item": "{{ route('blog.show', $post['slug']) }}"
            }
        ]
    }
    </script>
@endpush

@push('styles')
    @vite(['resources/css/blog.css'])
@endpush

@section('content')
<div class="blog-post">
    <div class="post-container">
        <!-- 面包屑导航 -->
        <nav class="breadcrumb-nav" aria-label="Breadcrumb Navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('home.index') }}" title="Back to Home">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z"/>
                        </svg>
                        Home
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('blog.index') }}" title="Back to Blog List">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        Tech Blog
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $post['title'] }}</li>
            </ol>
        </nav>
        
        <!-- 返回按钮 -->
        <div class="post-nav">
            <a href="{{ route('blog.index') }}" class="back-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                </svg>
                Back to Blog
            </a>
        </div>

        <!-- 文章头部 -->
        <header class="post-header">
            @if($post['image'])
                <div class="post-featured-image">
                    <img src="{{ $post['image'] }}" 
                         alt="{{ $post['title'] }} - {{ $post['category'] }} tech article cover image" 
                         width="1200" 
                         height="630"
                         decoding="async"
                         fetchpriority="high">
                </div>
            @endif
            
            <div class="post-meta">
                <div class="post-categories">
                    <span class="post-category">{{ $post['category'] }}</span>
                </div>
                <div class="post-info">
                    <span class="post-author">{{ $post['author'] }}</span>
                    <span class="post-date">{{ date('M d, Y', $post['published_at']) }}</span>
                    <span class="post-reading-time">{{ $post['reading_time'] }} min read</span>
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
                    <span class="share-label">Share Article:</span>
                    <div class="share-buttons">
                        <a href="#" class="share-btn" onclick="shareToWeibo()" title="Share to Weibo">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M8.5,8.5A1.5,1.5 0 0,1 10,10A1.5,1.5 0 0,1 8.5,11.5A1.5,1.5 0 0,1 7,10A1.5,1.5 0 0,1 8.5,8.5M15.5,8.5A1.5,1.5 0 0,1 17,10A1.5,1.5 0 0,1 15.5,11.5A1.5,1.5 0 0,1 14,10A1.5,1.5 0 0,1 15.5,8.5M12,17.23C10.25,17.23 8.71,16.5 7.81,15.42L9.23,14C9.68,14.72 10.75,15.17 12,15.17C13.25,15.17 14.32,14.72 14.77,14L16.19,15.42C15.29,16.5 13.75,17.23 12,17.23Z"/>
                            </svg>
                        </a>
                        <a href="#" class="share-btn" onclick="shareToQQ()" title="Share to QQ">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20Z"/>
                            </svg>
                        </a>
                        <a href="#" class="share-btn" onclick="copyLink()" title="Copy Link">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3.9,12C3.9,10.29 5.29,8.9 7,8.9H11V7H7A5,5 0 0,0 2,12A5,5 0 0,0 7,17H11V15.1H7C5.29,15.1 3.9,13.71 3.9,12M8,13H16V11H8V13M17,7H13V8.9H17C18.71,8.9 20.1,10.29 20.1,12C20.1,13.71 18.71,15.1 17,15.1H13V17H17A5,5 0 0,0 22,12A5,5 0 0,0 17,7Z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="post-updated">
                    <span class="updated-label">Last Updated:</span>
                    <span class="updated-time">{{ date('Y-m-d H:i', $post['mtime']) }}</span>
                </div>
            </div>
        </footer>

        <!-- 相关文章 -->
        @if(count($relatedPosts) > 0)
            <section class="related-posts">
                <h3 class="section-title">Related Articles</h3>
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

        <!-- 评论区域 -->
        <section class="comments-section">
            <h3 class="section-title">Comments (<span id="comments-count">{{ count($comments) }}</span>)</h3>
            
            <!-- 评论表单 -->
            <div class="comment-form-container">
                <form id="comment-form" class="comment-form" autocomplete="off">
                    @csrf
                    <div class="form-group">
                        <label for="author_name">Your Name (Optional)</label>
                        <input type="text" id="author_name" name="author_name" maxlength="50" placeholder="Leave blank to auto-generate random nickname">
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Comment Content *</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="4" 
                            maxlength="1000" 
                            placeholder="Please enter your comment..." 
                            required
                        ></textarea>
                        <small class="char-count">0/1000</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Post Comment</button>
                        <button type="button" class="random-name-btn" onclick="generateRandomName()">Random Name</button>
                    </div>
                </form>
            </div>
            
            <!-- 评论列表 -->
            <div class="comments-list" id="comments-list">
                @forelse($comments as $comment)
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-author">{{ $comment->author_name }}</span>
                            <span class="comment-time">{{ $comment->time_ago }}</span>
                        </div>
                        <div class="comment-content">
                            {{ strip_tags($comment->content) }}
                        </div>
                    </div>
                @empty
                    <div class="no-comments">
                        <p>No comments yet. Be the first to share your thoughts!</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- 导航链接 -->
        <nav class="post-navigation">
            <a href="{{ route('blog.index') }}" class="nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                </svg>
                All Articles
            </a>
            <a href="{{ route('reports.index') }}" class="nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                </svg>
                Lab Reports
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
        btn.title = 'Link copied!';
        setTimeout(() => {
            btn.title = originalTitle;
        }, 2000);
    }).catch(err => {
        console.error('Copy failed:', err);
    });
}

// 生成随机昵称
function generateRandomName() {
    const adjectives = ['Wise', 'Brave', 'Mysterious', 'Elegant', 'Smart', 'Clever', 'Witty', 'Humorous'];
    const nouns = ['Visitor', 'Reader', 'Passerby', 'Scholar', 'Explorer', 'Thinker', 'Observer', 'Traveler'];
    const adjective = adjectives[Math.floor(Math.random() * adjectives.length)];
    const noun = nouns[Math.floor(Math.random() * nouns.length)];
    const number = Math.floor(Math.random() * 900) + 100;
    document.getElementById('author_name').value = adjective + noun + number;
}

// 更新字符计数
function updateCharCount() {
    const content = document.getElementById('content').value;
    const charCount = document.querySelector('.char-count');
    if (charCount) {
        charCount.textContent = content.length + '/1000';
        
        if (content.length > 950) {
            charCount.style.color = '#ef4444';
        } else if (content.length > 800) {
            charCount.style.color = '#f59e0b';
        } else {
            charCount.style.color = '#6b7280';
        }
    }
}

// 显示消息提示
function showMessage(message, type = 'success') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    messageDiv.textContent = message;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        messageDiv.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(messageDiv);
        }, 300);
    }, 3000);
}

// 添加评论到列表
function addCommentToList(comment) {
    const commentsList = document.getElementById('comments-list');
    const noComments = commentsList.querySelector('.no-comments');
    
    if (noComments) {
        noComments.remove();
    }
    
    const commentItem = document.createElement('div');
    commentItem.className = 'comment-item new-comment';
    
    // 创建评论结构
    const commentHeader = document.createElement('div');
    commentHeader.className = 'comment-header';
    
    const authorSpan = document.createElement('span');
    authorSpan.className = 'comment-author';
    authorSpan.textContent = comment.author_name;
    
    const timeSpan = document.createElement('span');
    timeSpan.className = 'comment-time';
    timeSpan.textContent = comment.time_ago;
    
    commentHeader.appendChild(authorSpan);
    commentHeader.appendChild(timeSpan);
    
    const commentContent = document.createElement('div');
    commentContent.className = 'comment-content';
    // 使用textContent来避免HTML转义问题，并手动处理换行
    commentContent.textContent = comment.content;
    // 保持换行符的显示
    commentContent.style.whiteSpace = 'pre-wrap';
    
    commentItem.appendChild(commentHeader);
    commentItem.appendChild(commentContent);
    
    commentsList.insertBefore(commentItem, commentsList.firstChild);
    
    // 更新评论计数
    const countElement = document.getElementById('comments-count');
    const currentCount = parseInt(countElement.textContent);
    countElement.textContent = currentCount + 1;
    
    // 高亮新评论
    setTimeout(() => {
        commentItem.classList.remove('new-comment');
    }, 2000);
}

// 代码块复制功能
document.addEventListener('DOMContentLoaded', function() {
    const codeBlocks = document.querySelectorAll('pre code');
    codeBlocks.forEach(function(codeBlock) {
        const button = document.createElement('button');
        button.className = 'copy-code-btn';
        button.textContent = 'Copy';
        button.addEventListener('click', function() {
            navigator.clipboard.writeText(codeBlock.textContent).then(() => {
                button.textContent = 'Copied!';
                setTimeout(() => {
                    button.textContent = 'Copy';
                }, 2000);
            });
        });
        
        const pre = codeBlock.parentElement;
        pre.style.position = 'relative';
        pre.appendChild(button);
    });
    
    // 绑定评论表单提交
    const commentForm = document.getElementById('comment-form');
    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(commentForm);
        const submitBtn = commentForm.querySelector('.submit-btn');
        const originalText = submitBtn.textContent;
        
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;
        
        fetch(`{{ route('blog.comments.store', $post['slug']) }}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                addCommentToList(data.comment);
                commentForm.reset();
                updateCharCount();
            } else {
                showMessage(data.error || 'Failed to post comment', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Network error, please try again later', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // 绑定字符计数更新
    const contentTextarea = document.getElementById('content');
    if (contentTextarea) {
        // 使用多个事件来确保字符计数正确更新
        contentTextarea.addEventListener('input', updateCharCount);
        contentTextarea.addEventListener('keyup', updateCharCount);
        contentTextarea.addEventListener('paste', function() {
            setTimeout(updateCharCount, 10);
        });
    }
    
    // 确保空格键能够正常工作
    if (contentTextarea) {
        contentTextarea.addEventListener('keydown', function(e) {
            if (e.key === ' ' || e.keyCode === 32) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
        }, true);
    }
    
    // 初始化字符计数
    updateCharCount();
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