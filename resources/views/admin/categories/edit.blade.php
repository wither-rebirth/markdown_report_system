@extends('admin.layout')

@section('title', '编辑分类')
@section('page-title', '编辑分类')

@section('content')
<div class="page-header">
    <h1 class="page-header-title">📂 编辑分类</h1>
    <div class="page-header-actions">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 返回列表
        </a>
    </div>
</div>

<div style="margin: 1.5rem;">
    <div style="max-width: 600px;">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">分类信息</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name" class="form-label">分类名称 *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input {{ $errors->has('name') ? 'error' : '' }}"
                            value="{{ old('name', $category->name) }}"
                            placeholder="请输入分类名称"
                            required
                            maxlength="100"
                        >
                        @if($errors->has('name'))
                            <div class="form-error">{{ $errors->first('name') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="slug" class="form-label">分类别名</label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            class="form-input {{ $errors->has('slug') ? 'error' : '' }}"
                            value="{{ old('slug', $category->slug) }}"
                            placeholder="分类URL别名"
                            pattern="[a-z0-9\-]+"
                            maxlength="100"
                        >
                        <div class="form-help">只能包含小写字母、数字和连字符，用于生成分类URL</div>
                        @if($errors->has('slug'))
                            <div class="form-error">{{ $errors->first('slug') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">分类描述</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-textarea {{ $errors->has('description') ? 'error' : '' }}"
                            placeholder="分类的详细描述（可选）"
                            style="height: 100px;"
                            maxlength="500"
                        >{{ old('description', $category->description) }}</textarea>
                        <div class="form-help">简短描述此分类的内容和用途</div>
                        @if($errors->has('description'))
                            <div class="form-error">{{ $errors->first('description') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="sort_order" class="form-label">排序值</label>
                        <input 
                            type="number" 
                            id="sort_order" 
                            name="sort_order" 
                            class="form-input {{ $errors->has('sort_order') ? 'error' : '' }}"
                            value="{{ old('sort_order', $category->sort_order) }}"
                            min="1"
                            max="9999"
                            style="width: 120px;"
                        >
                        <div class="form-help">数值越小排序越靠前</div>
                        @if($errors->has('sort_order'))
                            <div class="form-error">{{ $errors->first('sort_order') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1" 
                                {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                                style="margin-right: 0.5rem;"
                            >
                            启用此分类
                        </label>
                        <div class="form-help">禁用的分类不会在前台显示</div>
                    </div>
                </div>
            </div>
            
            <!-- 分类统计 -->
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">分类统计</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; font-size: 0.875rem;">
                        <div>
                            <strong>创建时间：</strong><br>
                            {{ $category->created_at->format('Y-m-d H:i:s') }}
                        </div>
                        <div>
                            <strong>更新时间：</strong><br>
                            {{ $category->updated_at->format('Y-m-d H:i:s') }}
                        </div>
                        <div>
                            <strong>使用统计：</strong><br>
                            <span class="text-muted">文章数量功能正在开发中</span>
                        </div>
                        <div>
                            <strong>状态：</strong><br>
                            @if($category->is_active)
                                <span class="status-badge active">启用</span>
                            @else
                                <span class="status-badge inactive">禁用</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> 保存更改
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> 取消
                </a>
                <div style="margin-left: auto;">
                    <form action="{{ route('admin.categories.destroy', $category) }}" 
                          method="POST" 
                          style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-danger" 
                                data-confirm="确定要删除分类「{{ $category->name }}」吗？">
                            <i class="fas fa-trash"></i> 删除分类
                        </button>
                    </form>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// 自动生成slug（只在用户未手动修改时）
document.getElementById('name').addEventListener('input', function() {
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
</script>
@endpush
@endsection 