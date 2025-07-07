@extends('admin.layout')

@section('title', '仪表板')
@section('page-title', '仪表板')

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

@push('styles')
<style>
/* 仪表板专用样式 */
.dashboard-container {
    padding: var(--spacing-xl);
    max-width: 1400px;
    margin: 0 auto;
}

/* 欢迎区域 */
.welcome-section {
    margin-bottom: var(--spacing-2xl);
}

.welcome-content {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--info-color) 100%);
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
}

.welcome-content::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 100%;
    height: 200%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
    animation: float 20s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.welcome-text {
    position: relative;
    z-index: 1;
}

.welcome-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: var(--spacing-sm);
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.greeting-icon {
    font-size: 1.5rem;
    animation: wave 2s ease-in-out infinite;
}

@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(15deg); }
    75% { transform: rotate(-15deg); }
}

.welcome-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin: 0;
}

.welcome-actions {
    position: relative;
    z-index: 1;
}

/* 统计卡片 */
.stats-section {
    margin-bottom: var(--spacing-2xl);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--spacing-lg);
}

.stat-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    padding: var(--spacing-xl);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-200);
    transition: all var(--transition-base);
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.stat-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--spacing-lg);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
    position: relative;
    overflow: hidden;
}

.stat-icon::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.blog-stat .stat-icon { background: linear-gradient(135deg, var(--primary-color), var(--primary-hover)); }
.comment-stat .stat-icon { background: linear-gradient(135deg, var(--info-color), var(--info-hover)); }
.category-stat .stat-icon { background: linear-gradient(135deg, var(--success-color), var(--success-hover)); }
.tag-stat .stat-icon { background: linear-gradient(135deg, var(--warning-color), var(--warning-hover)); }

.stat-menu-btn {
    background: none;
    border: none;
    color: var(--gray-400);
    cursor: pointer;
    padding: var(--spacing-sm);
    border-radius: var(--radius-sm);
    transition: all var(--transition-fast);
}

.stat-menu-btn:hover {
    background: var(--gray-100);
    color: var(--gray-600);
}

.stat-card-body {
    margin-bottom: var(--spacing-lg);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--gray-800);
    margin-bottom: var(--spacing-xs);
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--gray-600);
    font-weight: 500;
    margin-bottom: var(--spacing-md);
}

.stat-detail {
    display: flex;
    gap: var(--spacing-sm);
}

.stat-detail-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 0.75rem;
    font-weight: 500;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
}

.stat-detail-item.success {
    background: var(--success-light);
    color: var(--success-hover);
}

.stat-detail-item.info {
    background: var(--info-light);
    color: var(--info-hover);
}

.stat-card-footer {
    border-top: 1px solid var(--gray-100);
    padding-top: var(--spacing-md);
}

.stat-link {
    color: var(--gray-600);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all var(--transition-fast);
}

.stat-link:hover {
    color: var(--primary-color);
}

.stat-link i {
    transition: transform var(--transition-fast);
}

.stat-link:hover i {
    transform: translateX(4px);
}

/* 内容区域 */
.content-section {
    margin-bottom: var(--spacing-2xl);
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: var(--spacing-xl);
}

/* 小部件 */
.dashboard-widget {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-200);
    overflow: hidden;
    transition: all var(--transition-base);
}

.dashboard-widget:hover {
    box-shadow: var(--shadow-md);
}

.widget-header {
    background: var(--gray-50);
    padding: var(--spacing-lg) var(--spacing-xl);
    border-bottom: 1px solid var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.widget-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-weight: 600;
    color: var(--gray-800);
    font-size: 1rem;
}

.widget-actions {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.widget-link {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: color var(--transition-fast);
}

.widget-link:hover {
    color: var(--primary-hover);
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 0.75rem;
    font-weight: 500;
}

.status-indicator.online {
    color: var(--success-color);
}

.status-indicator i {
    font-size: 0.5rem;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.widget-content {
    padding: var(--spacing-xl);
}

/* 评论列表 */
.comments-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.comment-item {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: var(--gray-50);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.comment-item:hover {
    background: var(--gray-100);
}

.comment-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-light);
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.comment-content {
    flex: 1;
}

.comment-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--spacing-xs);
}

.comment-author {
    font-weight: 600;
    color: var(--gray-800);
    font-size: 0.875rem;
}

.comment-status {
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    font-weight: 500;
}

.comment-status.approved {
    background: var(--success-light);
    color: var(--success-hover);
}

.comment-status.pending {
    background: var(--warning-light);
    color: var(--warning-hover);
}

.comment-text {
    color: var(--gray-600);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: var(--spacing-sm);
}

.comment-meta {
    display: flex;
    gap: var(--spacing-md);
    font-size: 0.75rem;
    color: var(--gray-500);
}

/* 快速操作网格 */
.quick-actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.quick-action-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-lg);
    background: var(--gray-50);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--gray-800);
    transition: all var(--transition-fast);
    border: 1px solid var(--gray-200);
}

.quick-action-item:hover {
    background: var(--bg-primary);
    box-shadow: var(--shadow-sm);
    transform: translateY(-2px);
    border-color: var(--primary-color);
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.action-icon.blog { background: linear-gradient(135deg, var(--primary-color), var(--primary-hover)); }
.action-icon.category { background: linear-gradient(135deg, var(--success-color), var(--success-hover)); }
.action-icon.tag { background: linear-gradient(135deg, var(--warning-color), var(--warning-hover)); }
.action-icon.comment { background: linear-gradient(135deg, var(--info-color), var(--info-hover)); }

.action-content {
    flex: 1;
}

.action-title {
    font-weight: 600;
    font-size: 0.875rem;
    margin-bottom: 2px;
}

.action-subtitle {
    font-size: 0.75rem;
    color: var(--gray-500);
}

/* 系统信息网格 */
.system-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-lg);
}

.info-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: var(--gray-50);
    border-radius: var(--radius-md);
}

.info-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    background: var(--primary-light);
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-bottom: 2px;
}

.info-value {
    font-weight: 600;
    color: var(--gray-800);
    font-size: 0.875rem;
}

/* 空状态 */
.widget-empty {
    text-align: center;
    padding: var(--spacing-2xl) var(--spacing-lg);
}

.empty-icon {
    font-size: 3rem;
    color: var(--gray-300);
    margin-bottom: var(--spacing-lg);
}

.empty-text h4 {
    color: var(--gray-600);
    margin-bottom: var(--spacing-sm);
    font-size: 1.125rem;
}

.empty-text p {
    color: var(--gray-500);
    font-size: 0.875rem;
    margin: 0;
}

/* 响应式设计 */
@media (max-width: 1200px) {
    .content-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .system-widget {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: var(--spacing-lg);
    }
    
    .welcome-content {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-lg);
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .system-info-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
@endsection 