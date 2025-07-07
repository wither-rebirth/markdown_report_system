@extends('admin.layout')

@section('title', '博客管理')
@section('page-title', '博客管理')

@section('content')
<div class="page-header">
    <h1 class="page-header-title">📝 博客文章管理</h1>
    <div class="page-header-actions">
        <a href="{{ route('admin.blog.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> 写新文章
        </a>
    </div>
</div>

<div class="card" style="margin: 1.5rem;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">文章列表</h3>
            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-input" 
                        style="width: 250px;" 
                        placeholder="搜索标题、作者..." 
                        value="{{ $search }}"
                    >
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> 搜索
                    </button>
                    @if($search)
                        <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> 清除
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        @if(count($posts) > 0)
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40%;">标题</th>
                            <th style="width: 15%;">作者</th>
                            <th style="width: 15%;">分类</th>
                            <th style="width: 15%;">更新时间</th>
                            <th style="width: 15%;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $post)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $post['title'] }}</strong>
                                        @if(!empty($post['excerpt']))
                                            <br>
                                            <small class="text-muted">{{ Str::limit($post['excerpt'], 80) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $post['author'] }}</td>
                                <td>
                                    @if($post['category'])
                                        <span class="status-badge active">{{ $post['category'] }}</span>
                                    @else
                                        <span class="text-muted">未分类</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ date('Y-m-d H:i', $post['mtime']) }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('blog.show', $post['slug']) }}" 
                                           class="btn btn-sm btn-secondary" 
                                           target="_blank" 
                                           title="预览">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.blog.edit', $post['slug']) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="编辑">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.blog.destroy', $post['slug']) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-confirm="确定要删除这篇文章吗？"
                                                    title="删除">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <i class="fas fa-file-alt" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">暂无文章</h3>
                <p style="color: #9ca3af; margin-bottom: 1.5rem;">
                    @if($search)
                        没有找到匹配"{{ $search }}"的文章
                    @else
                        还没有发布任何文章，<a href="{{ route('admin.blog.create') }}">立即创建第一篇文章</a>
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<style>
.empty-state a {
    color: #667eea;
    text-decoration: none;
}

.empty-state a:hover {
    text-decoration: underline;
}
</style>
@endsection 