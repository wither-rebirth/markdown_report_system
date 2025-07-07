@extends('admin.layout')

@section('title', '新建分类')
@section('page-title', '新建分类')

@push('styles')
@vite(['resources/css/admin/categories.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/categories.js'])
@endpush

@section('content')
<div style="margin: 1.5rem;">
    <div style="max-width: 600px;">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">分类信息</h3>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name" class="form-label">分类名称 *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input {{ $errors->has('name') ? 'error' : '' }}"
                            value="{{ old('name') }}"
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
                            value="{{ old('slug') }}"
                            placeholder="留空自动生成，用于URL"
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
                        >{{ old('description') }}</textarea>
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
                            value="{{ old('sort_order', 1) }}"
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
                                {{ old('is_active', true) ? 'checked' : '' }}
                                style="margin-right: 0.5rem;"
                            >
                            启用此分类
                        </label>
                        <div class="form-help">禁用的分类不会在前台显示</div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> 保存分类
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> 取消
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// 自动生成slug
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