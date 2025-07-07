@extends('admin.layout')

@section('title', '编辑文章')
@section('page-title', '编辑文章')

@section('content')
<div class="page-header">
    <h1 class="page-header-title">✏️ 编辑文章</h1>
    <div class="page-header-actions">
        <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 返回列表
        </a>
        <a href="{{ route('blog.show', $slug) }}" class="btn btn-outline" target="_blank">
            <i class="fas fa-eye"></i> 预览
        </a>
    </div>
</div>

<form action="{{ route('admin.blog.update', $slug) }}" method="POST" style="margin: 1.5rem;">
    @csrf
    @method('PUT')
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- 主要内容 -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">文章内容</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="title" class="form-label">文章标题 *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-input {{ $errors->has('title') ? 'error' : '' }}"
                            value="{{ old('title', $post['title']) }}"
                            placeholder="请输入文章标题"
                            required
                        >
                        @if($errors->has('title'))
                            <div class="form-error">{{ $errors->first('title') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="slug" class="form-label">文章别名</label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            class="form-input"
                            value="{{ $slug }}"
                            readonly
                            style="background-color: #f3f4f6; color: #6b7280;"
                        >
                        <div class="form-help">文章别名不可修改，以保持URL稳定性</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="excerpt" class="form-label">文章摘要</label>
                        <textarea 
                            id="excerpt" 
                            name="excerpt" 
                            class="form-textarea {{ $errors->has('excerpt') ? 'error' : '' }}"
                            placeholder="请输入文章摘要（可选）"
                            data-max-length="500"
                            style="height: 80px;"
                        >{{ old('excerpt', $post['excerpt']) }}</textarea>
                        <div class="form-help">简短描述文章内容，用于显示在文章列表中</div>
                        @if($errors->has('excerpt'))
                            <div class="form-error">{{ $errors->first('excerpt') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="content" class="form-label">文章正文 *</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            class="form-textarea {{ $errors->has('content') ? 'error' : '' }}"
                            placeholder="请输入文章正文（支持Markdown语法）"
                            style="height: 400px;"
                            required
                        >{{ old('content', $post['content']) }}</textarea>
                        <div class="form-help">支持Markdown语法，图片请使用相对路径：![描述](images/图片名.jpg)</div>
                        @if($errors->has('content'))
                            <div class="form-error">{{ $errors->first('content') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 侧边栏 -->
        <div>
            <!-- 发布设置 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">发布设置</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="author" class="form-label">作者 *</label>
                        <input 
                            type="text" 
                            id="author" 
                            name="author" 
                            class="form-input {{ $errors->has('author') ? 'error' : '' }}"
                            value="{{ old('author', $post['author']) }}"
                            required
                        >
                        @if($errors->has('author'))
                            <div class="form-error">{{ $errors->first('author') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input 
                                type="checkbox" 
                                name="published" 
                                value="1" 
                                {{ old('published', $post['published'] ?? true) ? 'checked' : '' }}
                                style="margin-right: 0.5rem;"
                            >
                            立即发布
                        </label>
                        <div class="form-help">取消勾选将保存为草稿</div>
                    </div>
                </div>
            </div>
            
            <!-- 分类设置 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">分类</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <select name="category_id" class="form-select">
                            <option value="">选择分类</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ old('category_id', $post['category_id']) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- 标签设置 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">标签</h3>
                </div>
                <div class="card-body">
                    @if($tags->count() > 0)
                        <div style="max-height: 200px; overflow-y: auto;">
                            @foreach($tags as $tag)
                                <label style="display: block; margin-bottom: 0.5rem;">
                                    <input 
                                        type="checkbox" 
                                        name="tags[]" 
                                        value="{{ $tag->id }}"
                                        {{ in_array($tag->id, old('tags', $postTags)) ? 'checked' : '' }}
                                        style="margin-right: 0.5rem;"
                                    >
                                    <span class="tag-name" data-color="{{ $tag->display_color }}">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="form-help">
                            <a href="{{ route('admin.tags.create') }}" target="_blank">还没有标签，创建第一个标签</a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- 特色图片 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">特色图片</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <input 
                            type="url" 
                            name="image" 
                            class="form-input {{ $errors->has('image') ? 'error' : '' }}"
                            value="{{ old('image', $post['image']) }}"
                            placeholder="图片URL（可选）"
                        >
                        <div class="form-help">文章封面图片的完整URL地址</div>
                        @if($errors->has('image'))
                            <div class="form-error">{{ $errors->first('image') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- 文件信息 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">文件信息</h3>
                </div>
                <div class="card-body">
                    <div style="font-size: 0.875rem; color: #6b7280;">
                        <div style="margin-bottom: 0.5rem;">
                            <strong>文件路径：</strong><br>
                            <code style="font-size: 0.75rem;">{{ $post['path'] }}</code>
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <strong>最后修改：</strong><br>
                            {{ date('Y-m-d H:i:s', $post['mtime']) }}
                        </div>
                        <div>
                            <strong>文件大小：</strong><br>
                            {{ number_format($post['size'] / 1024, 2) }} KB
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 操作按钮 -->
            <div class="d-flex gap-2 mb-2">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> 保存更改
                </button>
                <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> 取消
                </a>
            </div>
            
            <!-- 删除按钮 -->
            <form action="{{ route('admin.blog.destroy', $slug) }}" method="POST" style="width: 100%;">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="btn btn-danger" 
                        style="width: 100%;"
                        data-confirm="确定要永久删除这篇文章吗？此操作不可恢复！">
                    <i class="fas fa-trash"></i> 删除文章
                </button>
            </form>
        </div>
    </div>
</form>

@push('scripts')
<script>
// 设置标签颜色
document.querySelectorAll('.tag-name').forEach(function(span) {
    span.style.color = span.dataset.color;
});
</script>
@endpush

<style>
@media (max-width: 768px) {
    form > div:first-child {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection 