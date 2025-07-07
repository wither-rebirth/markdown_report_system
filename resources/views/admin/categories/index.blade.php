@extends('admin.layout')

@section('title', '分类管理')
@section('page-title', '分类管理')

@section('content')
<div class="card" style="margin: 1.5rem;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">分类列表</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 新建分类
                </a>
                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-select" style="width: 120px;">
                        <option value="">全部状态</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>启用</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>禁用</option>
                    </select>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-input" 
                        style="width: 200px;" 
                        placeholder="搜索分类名称..." 
                        value="{{ request('search') }}"
                    >
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> 搜索
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> 清除
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        @if($categories->count() > 0)
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 30%;">分类名称</th>
                            <th style="width: 20%;">别名</th>
                            <th style="width: 25%;">描述</th>
                            <th style="width: 10%;">排序</th>
                            <th style="width: 8%;">状态</th>
                            <th style="width: 7%;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="categories-tbody">
                        @foreach($categories as $category)
                            <tr data-id="{{ $category->id }}">
                                <td>
                                    <div>
                                        <strong>{{ $category->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            创建时间：{{ $category->created_at->format('Y-m-d') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.875rem;">{{ $category->slug }}</code>
                                </td>
                                <td>
                                    @if($category->description)
                                        <span title="{{ $category->description }}">
                                            {{ Str::limit($category->description, 50) }}
                                        </span>
                                    @else
                                        <span class="text-muted">未填写</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline move-up" 
                                                data-id="{{ $category->id }}"
                                                title="上移">
                                            <i class="fas fa-chevron-up"></i>
                                        </button>
                                        <span style="font-weight: 600; min-width: 30px; text-align: center;">
                                            {{ $category->sort_order }}
                                        </span>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline move-down" 
                                                data-id="{{ $category->id }}"
                                                title="下移">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <label class="toggle-switch" title="点击切换状态">
                                        <input type="checkbox" 
                                               data-id="{{ $category->id }}"
                                               {{ $category->is_active ? 'checked' : '' }}
                                               class="status-toggle">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.categories.edit', $category) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="编辑">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-confirm="确定要删除分类「{{ $category->name }}」吗？"
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
            
            @if($categories->hasPages())
                <div style="padding: 1rem;">
                    {{ $categories->links() }}
                </div>
            @endif
        @else
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <i class="fas fa-folder-open" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">暂无分类</h3>
                <p style="color: #9ca3af; margin-bottom: 1.5rem;">
                    @if(request('search'))
                        没有找到匹配"{{ request('search') }}"的分类
                    @else
                        还没有创建任何分类，<a href="{{ route('admin.categories.create') }}">立即创建第一个分类</a>
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// 状态切换
document.querySelectorAll('.status-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        const id = this.dataset.id;
        const isActive = this.checked;
        
        fetch(`{{ route('admin.categories.index') }}/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ is_active: isActive })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('状态更新成功', 'success');
            } else {
                this.checked = !isActive; // 回滚
                showMessage('状态更新失败', 'error');
            }
        })
        .catch(error => {
            this.checked = !isActive; // 回滚
            showMessage('网络错误', 'error');
        });
    });
});

// 排序移动
document.querySelectorAll('.move-up, .move-down').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const direction = this.classList.contains('move-up') ? 'up' : 'down';
        
        fetch(`{{ route('admin.categories.index') }}/${id}/move`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ direction: direction })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showMessage('排序更新失败', 'error');
            }
        })
        .catch(error => {
            showMessage('网络错误', 'error');
        });
    });
});
</script>
@endpush

<style>
.empty-state a {
    color: #667eea;
    text-decoration: none;
}

.empty-state a:hover {
    text-decoration: underline;
}

.move-up, .move-down {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
    cursor: pointer;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 20px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #4CAF50;
}

input:checked + .toggle-slider:before {
    transform: translateX(20px);
}
</style>
@endsection 