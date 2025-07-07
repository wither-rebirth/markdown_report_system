@extends('admin.layout')

@section('title', '标签管理')
@section('page-title', '标签管理')

@section('content')
<div class="page-header">
    <h1 class="page-header-title">🏷️ 标签管理</h1>
    <div class="page-header-actions">
        <a href="{{ route('admin.tags.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> 新建标签
        </a>
    </div>
</div>

<div class="card" style="margin: 1.5rem;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">标签列表</h3>
            <div class="d-flex gap-2">
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
                        placeholder="搜索标签名称..." 
                        value="{{ request('search') }}"
                    >
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> 搜索
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> 清除
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        @if($tags->count() > 0)
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">
                                <input type="checkbox" id="select-all" style="margin: 0;">
                            </th>
                            <th style="width: 30%;">标签名称</th>
                            <th style="width: 20%;">别名</th>
                            <th style="width: 15%;">颜色</th>
                            <th style="width: 15%;">使用统计</th>
                            <th style="width: 8%;">状态</th>
                            <th style="width: 7%;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tags as $tag)
                            <tr>
                                <td>
                                    <input type="checkbox" 
                                           name="selected_tags[]" 
                                           value="{{ $tag->id }}" 
                                           class="tag-checkbox"
                                           style="margin: 0;">
                                </td>
                                <td>
                                    <div>
                                        <strong class="tag-name-display" data-color="{{ $tag->display_color }}">{{ $tag->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            创建：{{ $tag->created_at->format('Y-m-d') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.875rem;">{{ $tag->slug }}</code>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="tag-color-box" data-bg-color="{{ $tag->display_color }}" style="width: 20px; height: 20px; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
                                        <code style="font-size: 0.75rem;">{{ $tag->color ?: '默认' }}</code>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">统计功能开发中</span>
                                </td>
                                <td>
                                    <label class="toggle-switch" title="点击切换状态">
                                        <input type="checkbox" 
                                               data-id="{{ $tag->id }}"
                                               {{ $tag->is_active ? 'checked' : '' }}
                                               class="status-toggle">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.tags.edit', $tag) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="编辑">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.tags.destroy', $tag) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-confirm="确定要删除标签「{{ $tag->name }}」吗？"
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
            
            <!-- 批量操作 -->
            <div style="padding: 1rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; display: none;" id="bulk-actions">
                <div class="d-flex justify-content-between align-items-center">
                    <span id="selected-count">已选择 0 个标签</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success" id="bulk-enable">
                            <i class="fas fa-check"></i> 启用
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" id="bulk-disable">
                            <i class="fas fa-times"></i> 禁用
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="bulk-delete">
                            <i class="fas fa-trash"></i> 删除
                        </button>
                    </div>
                </div>
            </div>
            
            @if($tags->hasPages())
                <div style="padding: 1rem;">
                    {{ $tags->links() }}
                </div>
            @endif
        @else
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <i class="fas fa-tags" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">暂无标签</h3>
                <p style="color: #9ca3af; margin-bottom: 1.5rem;">
                    @if(request('search'))
                        没有找到匹配"{{ request('search') }}"的标签
                    @else
                        还没有创建任何标签，<a href="{{ route('admin.tags.create') }}">立即创建第一个标签</a>
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// 设置标签颜色
document.querySelectorAll('.tag-name-display').forEach(function(element) {
    element.style.color = element.dataset.color;
});

document.querySelectorAll('.tag-color-box').forEach(function(element) {
    element.style.backgroundColor = element.dataset.bgColor;
});

// 全选功能
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.tag-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkActions();
});

// 单个选择框
document.querySelectorAll('.tag-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const selected = document.querySelectorAll('.tag-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = `已选择 ${selected.length} 个标签`;
    } else {
        bulkActions.style.display = 'none';
    }
    
    // 更新全选框状态
    const allCheckboxes = document.querySelectorAll('.tag-checkbox');
    const selectAll = document.getElementById('select-all');
    selectAll.checked = selected.length === allCheckboxes.length;
    selectAll.indeterminate = selected.length > 0 && selected.length < allCheckboxes.length;
}

// 状态切换
document.querySelectorAll('.status-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        const id = this.dataset.id;
        const isActive = this.checked;
        
        fetch(`{{ route('admin.tags.index') }}/${id}/toggle`, {
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
                this.checked = !isActive;
                showMessage('状态更新失败', 'error');
            }
        })
        .catch(error => {
            this.checked = !isActive;
            showMessage('网络错误', 'error');
        });
    });
});

// 批量操作
document.getElementById('bulk-enable').addEventListener('click', function() {
    bulkAction('enable', '确定要启用选中的标签吗？');
});

document.getElementById('bulk-disable').addEventListener('click', function() {
    bulkAction('disable', '确定要禁用选中的标签吗？');
});

document.getElementById('bulk-delete').addEventListener('click', function() {
    bulkAction('delete', '确定要删除选中的标签吗？此操作不可恢复！');
});

function bulkAction(action, confirmMessage) {
    const selected = Array.from(document.querySelectorAll('.tag-checkbox:checked')).map(cb => cb.value);
    
    if (selected.length === 0) {
        showMessage('请先选择要操作的标签', 'warning');
        return;
    }
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    fetch(`{{ route('admin.tags.index') }}/bulk`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            action: action, 
            ids: selected 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            location.reload();
        } else {
            showMessage(data.message || '操作失败', 'error');
        }
    })
    .catch(error => {
        showMessage('网络错误', 'error');
    });
}
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