@extends('admin.layout')

@section('title', '标签管理')
@section('page-title', '标签管理')

@section('content')
<div class="card" style="margin: 1.5rem;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">标签列表</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.tags.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 新建标签
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
                            <th style="width: 35%;">标签名称</th>
                            <th style="width: 25%;">别名</th>
                            <th style="width: 20%;">颜色</th>
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
                                    <div class="tag-info">
                                        <div class="tag-name-wrapper">
                                            <span class="tag-preview" data-color="{{ $tag->display_color }}">
                                                {{ $tag->name }}
                                            </span>
                                            <strong class="tag-name-display">{{ $tag->name }}</strong>
                                        </div>
                                        <small class="tag-meta">
                                            创建：{{ $tag->created_at->format('Y-m-d') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.875rem;">{{ $tag->slug }}</code>
                                </td>
                                <td>
                                    <div class="tag-color-display">
                                        <div class="tag-color-swatch" data-bg-color="{{ $tag->display_color }}"></div>
                                        <code class="color-code">{{ $tag->color ?: '默认' }}</code>
                                    </div>
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
            <div class="bulk-actions-bar" id="bulk-actions">
                <div class="bulk-actions-content">
                    <div class="bulk-actions-info">
                        <i class="fas fa-check-square"></i>
                        <span id="selected-count">已选择 0 个标签</span>
                    </div>
                    <div class="bulk-actions-buttons">
                        <button type="button" class="bulk-action-btn bulk-action-enable" id="bulk-enable">
                            <i class="fas fa-check"></i> 启用
                        </button>
                        <button type="button" class="bulk-action-btn bulk-action-disable" id="bulk-disable">
                            <i class="fas fa-ban"></i> 禁用
                        </button>
                        <button type="button" class="bulk-action-btn bulk-action-delete" id="bulk-delete">
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
document.querySelectorAll('.tag-preview').forEach(function(element) {
    element.style.backgroundColor = element.dataset.color;
});

document.querySelectorAll('.tag-color-swatch').forEach(function(element) {
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
        // 添加动画效果
        bulkActions.style.animation = 'slideDown 0.3s ease-out';
    } else {
        bulkActions.style.display = 'none';
    }
    
    // 更新全选框状态
    const allCheckboxes = document.querySelectorAll('.tag-checkbox');
    const selectAll = document.getElementById('select-all');
    selectAll.checked = selected.length === allCheckboxes.length;
    selectAll.indeterminate = selected.length > 0 && selected.length < allCheckboxes.length;
}

// 状态切换 - 添加防抖和加载状态
let toggleTimeout = null;
document.querySelectorAll('.status-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        const id = this.dataset.id;
        const isActive = this.checked;
        const toggleElement = this;
        
        // 防止重复点击
        if (toggleElement.disabled) return;
        
        // 清除之前的timeout
        if (toggleTimeout) {
            clearTimeout(toggleTimeout);
        }
        
        // 禁用开关，显示加载状态
        toggleElement.disabled = true;
        const slider = toggleElement.nextElementSibling;
        slider.style.opacity = '0.6';
        
        toggleTimeout = setTimeout(() => {
            fetch(`{{ route('admin.tags.index') }}/${id}/toggle-status`, {
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
                    toggleElement.checked = !isActive;
                    showMessage('状态更新失败', 'error');
                }
            })
            .catch(error => {
                toggleElement.checked = !isActive;
                showMessage('网络错误', 'error');
            })
            .finally(() => {
                // 恢复开关状态
                toggleElement.disabled = false;
                slider.style.opacity = '1';
                toggleTimeout = null;
            });
        }, 300); // 300ms 防抖
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

// 批量操作 - 添加加载状态和防抖
let bulkActionInProgress = false;
function bulkAction(action, confirmMessage) {
    // 防止重复操作
    if (bulkActionInProgress) {
        return;
    }
    
    const selected = Array.from(document.querySelectorAll('.tag-checkbox:checked')).map(cb => cb.value);
    
    if (selected.length === 0) {
        showMessage('请先选择要操作的标签', 'warning');
        return;
    }
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // 设置加载状态
    bulkActionInProgress = true;
    const bulkButtons = document.querySelectorAll('#bulk-actions button');
    bulkButtons.forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.6';
    });
    
    fetch(`{{ route('admin.tags.bulk-action') }}`, {
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
            // 延迟刷新，让用户看到成功消息
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showMessage(data.message || '操作失败', 'error');
        }
    })
    .catch(error => {
        showMessage('网络错误', 'error');
    })
    .finally(() => {
        // 恢复按钮状态
        bulkActionInProgress = false;
        bulkButtons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
        });
    });
}
</script>
@endpush

<style>
/* 标签管理专属样式 */
.tag-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.tag-name-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tag-preview {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
    background: var(--primary-color);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.tag-name-display {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-800);
}

.tag-meta {
    color: var(--gray-500);
    font-size: 0.75rem;
}

.tag-color-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tag-color-swatch {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: 2px solid var(--gray-200);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.tag-color-swatch:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.color-code {
    font-size: 0.75rem;
    color: var(--gray-600);
    background: var(--gray-100);
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
}

/* 批量操作栏样式 */
.bulk-actions-bar {
    display: none;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    border-top: 1px solid var(--primary-border);
    animation: slideDown 0.3s ease-out;
}

.bulk-actions-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
}

.bulk-actions-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.bulk-actions-info i {
    font-size: 1.1rem;
    opacity: 0.9;
}

.bulk-actions-buttons {
    display: flex;
    gap: 0.75rem;
}

.bulk-action-btn {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    backdrop-filter: blur(4px);
}

.bulk-action-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.bulk-action-btn:active {
    transform: translateY(0);
}

.bulk-action-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.bulk-action-enable:hover {
    background: rgba(34, 197, 94, 0.2);
}

.bulk-action-disable:hover {
    background: rgba(251, 146, 60, 0.2);
}

.bulk-action-delete:hover {
    background: rgba(239, 68, 68, 0.2);
}

/* 切换开关禁用状态 */
.toggle-switch input:disabled + .toggle-slider {
    opacity: 0.6;
    cursor: not-allowed;
}

/* 空状态样式 */
.empty-state a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.empty-state a:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

/* 动画 */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* 响应式优化 */
@media (max-width: 768px) {
    .bulk-actions-content {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
    }
    
    .bulk-actions-buttons {
        width: 100%;
        justify-content: center;
    }
    
    .tag-name-wrapper {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>
@endsection 