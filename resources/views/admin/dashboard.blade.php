@extends('admin.layout')

@section('title', '仪表板')
@section('page-title', '仪表板')

@push('styles')
@vite(['resources/css/admin/dashboard.css'])
@endpush

@section('content')
<div class="dashboard-container">
    <!-- 欢迎区域 -->
    <div class="welcome-section">
        <div class="welcome-content">
            <div class="welcome-text">
                <h2 class="welcome-title">
                    <span class="greeting-icon">👋</span>
                    欢迎回来，{{ Auth::user()->name }}
                </h2>
                <p class="welcome-subtitle">
                    今天是 {{ date('Y年m月d日') }}，{{ ['周日', '周一', '周二', '周三', '周四', '周五', '周六'][date('w')] }}
                </p>
            </div>
            <div class="welcome-actions">
                <a href="{{ route('admin.blog.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>写新文章</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 统计卡片网格 -->
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-card blog-stat">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-menu">
                        <button class="stat-menu-btn">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-number">{{ $blogStats['total'] }}</div>
                    <div class="stat-label">博客文章</div>
                    <div class="stat-detail">
                        <span class="stat-detail-item success">
                            <i class="fas fa-check"></i>
                            已发布 {{ $blogStats['published'] }}
                        </span>
                    </div>
                </div>
                <div class="stat-card-footer">
                    <a href="{{ route('admin.blog.index') }}" class="stat-link">
                        查看全部 <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card comment-stat">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-menu">
                        <button class="stat-menu-btn">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-number">{{ $commentStats['total'] }}</div>
                    <div class="stat-label">评论总数</div>
                    <div class="stat-detail">
                        <span class="stat-detail-item info">
                            <i class="fas fa-calendar-day"></i>
                            今日 {{ $commentStats['today'] }}
                        </span>
                    </div>
                </div>
                <div class="stat-card-footer">
                    <a href="{{ route('admin.comments.index') }}" class="stat-link">
                        管理评论 <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card category-stat">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="stat-menu">
                        <button class="stat-menu-btn">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-number">{{ $categoryCount }}</div>
                    <div class="stat-label">分类数量</div>
                    <div class="stat-detail">
                        <span class="stat-detail-item success">
                            <i class="fas fa-check-circle"></i>
                            已激活
                        </span>
                    </div>
                </div>
                <div class="stat-card-footer">
                    <a href="{{ route('admin.categories.index') }}" class="stat-link">
                        管理分类 <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card tag-stat">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-menu">
                        <button class="stat-menu-btn">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-number">{{ $tagCount }}</div>
                    <div class="stat-label">标签数量</div>
                    <div class="stat-detail">
                        <span class="stat-detail-item info">
                            <i class="fas fa-tag"></i>
                            可使用
                        </span>
                    </div>
                </div>
                <div class="stat-card-footer">
                    <a href="{{ route('admin.tags.index') }}" class="stat-link">
                        管理标签 <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 主要内容区域 -->
    <div class="content-section">
        <div class="content-grid">
            <!-- 最新评论 -->
            <div class="dashboard-widget comments-widget">
                <div class="widget-header">
                    <div class="widget-title">
                        <i class="fas fa-comment-dots"></i>
                        <span>最新评论</span>
                    </div>
                    <div class="widget-actions">
                        <a href="{{ route('admin.comments.index') }}" class="widget-link">
                            查看全部
                        </a>
                    </div>
                </div>
                <div class="widget-content">
                    @if($latestComments->count() > 0)
                        <div class="comments-list">
                            @foreach($latestComments as $comment)
                                <div class="comment-item">
                                    <div class="comment-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <div class="comment-author">{{ $comment->author_name }}</div>
                                            <div class="comment-status {{ $comment->is_approved ? 'approved' : 'pending' }}">
                                                {{ $comment->is_approved ? '已审核' : '待审核' }}
                                            </div>
                                        </div>
                                        <div class="comment-text">
                                            {{ Str::limit($comment->content, 100) }}
                                        </div>
                                        <div class="comment-meta">
                                            <span class="comment-post">{{ $comment->blog_slug }}</span>
                                            <span class="comment-time">{{ $comment->time_ago }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="widget-empty">
                            <div class="empty-icon">
                                <i class="fas fa-comment-slash"></i>
                            </div>
                            <div class="empty-text">
                                <h4>暂无评论</h4>
                                <p>还没有收到任何评论</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>



            <!-- 系统信息 -->
            <div class="dashboard-widget system-widget">
                <div class="widget-header">
                    <div class="widget-title">
                        <i class="fas fa-server"></i>
                        <span>系统状态</span>
                    </div>
                    <div class="widget-actions">
                        <div class="status-indicator online">
                            <i class="fas fa-circle"></i>
                            <span>运行正常</span>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="system-info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">当前用户</div>
                                <div class="info-value">{{ Auth::user()->name }}</div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">登录时间</div>
                                <div class="info-value">{{ now()->format('H:i') }}</div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fab fa-laravel"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Laravel</div>
                                <div class="info-value">{{ app()->version() }}</div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fab fa-php"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">PHP</div>
                                <div class="info-value">{{ PHP_VERSION }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection 