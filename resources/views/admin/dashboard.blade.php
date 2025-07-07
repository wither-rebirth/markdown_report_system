@extends('admin.layout')

@section('title', '仪表板')
@section('page-title', '仪表板')

@section('content')
<div class="dashboard">
    <!-- 统计卡片 -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blog">
                <i class="fas fa-blog"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $blogStats['total'] }}</h3>
                <p>博客文章</p>
                <span class="stat-detail">已发布 {{ $blogStats['published'] }}</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon comments">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $commentStats['total'] }}</h3>
                <p>评论总数</p>
                <span class="stat-detail">今日 {{ $commentStats['today'] }}</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon categories">
                <i class="fas fa-folder"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $categoryCount }}</h3>
                <p>分类数量</p>
                <span class="stat-detail">已激活</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon tags">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $tagCount }}</h3>
                <p>标签数量</p>
                <span class="stat-detail">可使用</span>
            </div>
        </div>
    </div>
    
    <!-- 主要内容区域 -->
    <div class="dashboard-grid">
        <!-- 最新评论 -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3><i class="fas fa-comment-dots"></i> 最新评论</h3>
                <a href="{{ route('admin.comments.index') }}" class="widget-link">查看全部</a>
            </div>
            <div class="widget-content">
                @if($latestComments->count() > 0)
                    <div class="comments-list">
                        @foreach($latestComments as $comment)
                            <div class="comment-item">
                                <div class="comment-author">
                                    <strong>{{ $comment->author_name }}</strong>
                                    <span class="comment-status {{ $comment->is_approved ? 'approved' : 'pending' }}">
                                        {{ $comment->is_approved ? '已审核' : '待审核' }}
                                    </span>
                                </div>
                                <div class="comment-content">
                                    {{ Str::limit($comment->content, 80) }}
                                </div>
                                <div class="comment-meta">
                                    <span>{{ $comment->blog_slug }}</span> • 
                                    <span>{{ $comment->time_ago }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-comment-slash"></i>
                        <p>暂无评论</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- 快速操作 -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3><i class="fas fa-rocket"></i> 快速操作</h3>
            </div>
            <div class="widget-content">
                <div class="quick-actions">
                    <a href="{{ route('admin.blog.create') }}" class="quick-action">
                        <i class="fas fa-plus"></i>
                        <span>写博客</span>
                    </a>
                    
                    <a href="{{ route('admin.categories.create') }}" class="quick-action">
                        <i class="fas fa-folder-plus"></i>
                        <span>新建分类</span>
                    </a>
                    
                    <a href="{{ route('admin.tags.create') }}" class="quick-action">
                        <i class="fas fa-tag"></i>
                        <span>新建标签</span>
                    </a>
                    
                    <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}" class="quick-action">
                        <i class="fas fa-eye"></i>
                        <span>审核评论</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- 系统信息 -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3><i class="fas fa-info-circle"></i> 系统信息</h3>
            </div>
            <div class="widget-content">
                <div class="system-info">
                    <div class="info-item">
                        <span class="info-label">当前用户：</span>
                        <span class="info-value">{{ Auth::user()->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">登录时间：</span>
                        <span class="info-value">{{ now()->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Laravel版本：</span>
                        <span class="info-value">{{ app()->version() }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">PHP版本：</span>
                        <span class="info-value">{{ PHP_VERSION }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard {
    padding: 1.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.25rem;
    color: white;
}

.stat-icon.blog { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon.comments { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-icon.categories { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-icon.tags { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

.stat-info h3 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.stat-info p {
    color: #6b7280;
    margin: 0.25rem 0;
    font-size: 0.875rem;
}

.stat-detail {
    color: #059669;
    font-size: 0.75rem;
    font-weight: 500;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.dashboard-widget {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.widget-header {
    padding: 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.widget-header h3 {
    color: #1f2937;
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
}

.widget-link {
    color: #667eea;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
}

.widget-content {
    padding: 1.25rem;
}

.comments-list {
    space-y: 1rem;
}

.comment-item {
    padding-bottom: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.comment-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.comment-author {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.comment-status {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-weight: 500;
}

.comment-status.approved {
    background: #d1fae5;
    color: #065f46;
}

.comment-status.pending {
    background: #fef3c7;
    color: #92400e;
}

.comment-content {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.comment-meta {
    color: #9ca3af;
    font-size: 0.75rem;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    text-decoration: none;
    color: #374151;
    transition: background-color 0.15s ease-in-out;
}

.quick-action:hover {
    background: #f3f4f6;
}

.quick-action i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #667eea;
}

.system-info {
    space-y: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.info-value {
    color: #1f2937;
    font-size: 0.875rem;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}
</style>
@endsection 