@extends('admin.layout')

@section('title', '写新文章')
@section('page-title', '写新文章')

@section('content')
<form action="{{ route('admin.blog.store') }}" method="POST" style="margin: 1.5rem;">
    @csrf
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- 主要内容 -->
        <div>
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">文章内容</h3>
                        <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="title" class="form-label">文章标题 *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-input {{ $errors->has('title') ? 'error' : '' }}"
                            value="{{ old('title') }}"
                            placeholder="请输入文章标题"
                            required
                        >
                        @if($errors->has('title'))
                            <div class="form-error">{{ $errors->first('title') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="slug" class="form-label">文章别名 *</label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            class="form-input {{ $errors->has('slug') ? 'error' : '' }}"
                            value="{{ old('slug') }}"
                            placeholder="文章URL别名，留空自动生成"
                            pattern="[a-z0-9\-]+"
                        >
                        <div class="form-help">只能包含小写字母、数字和连字符，用于生成文章URL</div>
                        @if($errors->has('slug'))
                            <div class="form-error">{{ $errors->first('slug') }}</div>
                        @endif
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
                        >{{ old('excerpt') }}</textarea>
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
                        >{{ old('content') }}</textarea>
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
                            value="{{ old('author', Auth::user()->name) }}"
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
                                {{ old('published', true) ? 'checked' : '' }}
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
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($categories->count() == 0)
                            <div class="form-help">
                                <a href="{{ route('admin.categories.create') }}" target="_blank">还没有分类，创建第一个分类</a>
                            </div>
                        @endif
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
                                        {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
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
                            value="{{ old('image') }}"
                            placeholder="图片URL（可选）"
                        >
                        <div class="form-help">文章封面图片的完整URL地址</div>
                        @if($errors->has('image'))
                            <div class="form-error">{{ $errors->first('image') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- 操作按钮 -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> 保存文章
                </button>
                <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> 取消
                </a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
// 自动生成slug
document.getElementById('title').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.dataset.userModified) {
        slugInput.value = generateSlug(this.value);
    }
});

document.getElementById('slug').addEventListener('input', function() {
    this.dataset.userModified = 'true';
});

function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

// 设置标签颜色
document.querySelectorAll('.tag-name').forEach(function(span) {
    span.style.color = span.dataset.color;
});
</script>
@endpush

<style>
@media (max-width: 768px) {
    form > div {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection 