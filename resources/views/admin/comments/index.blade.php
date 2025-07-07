@extends('admin.layout')

@section('title', '评论管理')
@section('page-title', '评论管理')

@section('content')
<div class="page-header">
    <h1 class="page-header-title">💬 评论管理</h1>
    <div class="page-header-actions">
        <button type="button" class="btn btn-success" id="batch-approve" style="display: none;">
            <i class="fas fa-check"></i> 批量通过
        </button>
        <button type="button" class="btn btn-warning" id="batch-spam" style="display: none;">
            <i class="fas fa-ban"></i> 标记垃圾
        </button>
        <button type="button" class="btn btn-danger" id="batch-delete" style="display: none;">
            <i class="fas fa-trash"></i> 批量删除
        </button>
    </div>
</div>

<div class="card" style="margin: 1.5rem;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">评论列表</h3>
            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-select" style="width: 120px;">
                        <option value="">全部状态</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>待审核</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>已通过</option>
                        <option value="spam" {{ request('status') == 'spam' ? 'selected' : '' }}>垃圾评论</option>
                    </select>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-input" 
                        style="width: 200px;" 
                        placeholder="搜索内容、作者..." 
                        value="{{ request('search') }}"
                    >
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> 搜索
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> 清除
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        @if($comments->count() > 0)
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">
                                <input type="checkbox" id="select-all" style="margin: 0;">
                            </th>
                            <th style="width: 20%;">评论者</th>
                            <th style="width: 30%;">评论内容</th>
                            <th style="width: 15%;">文章</th>
                            <th style="width: 10%;">提交时间</th>
                            <th style="width: 8%;">状态</th>
                            <th style="width: 12%;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comments as $comment)
                            <tr class="comment-row {{ $comment->status === 'pending' ? 'comment-pending' : '' }}">
                                <td>
                                    <input type="checkbox" 
                                           name="selected_comments[]" 
                                           value="{{ $comment->id }}" 
                                           class="comment-checkbox"
                                           style="margin: 0;">
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $comment->author_name }}</strong>
                                        @if($comment->author_email)
                                            <br>
                                            <small class="text-muted">{{ $comment->author_email }}</small>
                                        @endif
                                        @if($comment->author_website)
                                            <br>
                                            <small>
                                                <a href="{{ $comment->author_website }}" 
                                                   target="_blank" 
                                                   style="color: #667eea; text-decoration: none;">
                                                    <i class="fas fa-external-link-alt"></i> 网站
                                                </a>
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="max-height: 80px; overflow-y: auto;">
                                        {{ Str::limit($comment->content, 120) }}
                                    </div>
                                    @if($comment->parent_id)
                                        <small class="text-muted">
                                            <i class="fas fa-reply"></i> 回复评论
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('blog.show', $comment->blog_slug) }}" 
                                       target="_blank" 
                                       style="color: #667eea; text-decoration: none;">
                                        {{ Str::limit($comment->blog_slug, 20) }}
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>
                                <td>
                                    <small>{{ $comment->created_at->format('m-d H:i') }}</small>
                                </td>
                                <td>
                                    @if($comment->status === 'pending')
                                        <span class="status-badge warning">待审核</span>
                                    @elseif($comment->status === 'approved')
                                        <span class="status-badge active">已通过</span>
                                    @elseif($comment->status === 'spam')
                                        <span class="status-badge danger">垃圾</span>
                                    @else
                                        <span class="status-badge inactive">未知</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($comment->status === 'pending')
                                            <button type="button" 
                                                    class="btn btn-sm btn-success approve-btn" 
                                                    data-id="{{ $comment->id }}"
                                                    title="通过">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('admin.comments.edit', $comment) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="编辑">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if($comment->status !== 'spam')
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning spam-btn" 
                                                    data-id="{{ $comment->id }}"
                                                    title="标记垃圾">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                        
                                        <form action="{{ route('admin.comments.destroy', $comment) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-confirm="确定要删除这条评论吗？"
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
            
            @if($comments->hasPages())
                <div style="padding: 1rem;">
                    {{ $comments->links() }}
                </div>
            @endif
        @else
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <i class="fas fa-comments" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">暂无评论</h3>
                <p style="color: #9ca3af; margin-bottom: 1.5rem;">
                    @if(request('search'))
                        没有找到匹配"{{ request('search') }}"的评论
                    @else
                        还没有收到任何评论
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// 全选功能
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.comment-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBatchActions();
});

// 单个选择框
document.querySelectorAll('.comment-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBatchActions);
});

function updateBatchActions() {
    const selected = document.querySelectorAll('.comment-checkbox:checked');
    const batchBtns = ['batch-approve', 'batch-spam', 'batch-delete'];
    
    batchBtns.forEach(btnId => {
        const btn = document.getElementById(btnId);
        btn.style.display = selected.length > 0 ? 'inline-flex' : 'none';
    });
    
    // 更新全选框状态
    const allCheckboxes = document.querySelectorAll('.comment-checkbox');
    const selectAll = document.getElementById('select-all');
    selectAll.checked = selected.length === allCheckboxes.length;
    selectAll.indeterminate = selected.length > 0 && selected.length < allCheckboxes.length;
}

// 单个操作
document.querySelectorAll('.approve-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        updateCommentStatus(this.dataset.id, 'approved');
    });
});

document.querySelectorAll('.spam-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        updateCommentStatus(this.dataset.id, 'spam');
    });
});

// 批量操作
document.getElementById('batch-approve').addEventListener('click', function() {
    batchAction('approve', '确定要通过选中的评论吗？');
});

document.getElementById('batch-spam').addEventListener('click', function() {
    batchAction('spam', '确定要将选中的评论标记为垃圾吗？');
});

document.getElementById('batch-delete').addEventListener('click', function() {
    batchAction('delete', '确定要删除选中的评论吗？此操作不可恢复！');
});

function updateCommentStatus(id, status) {
    fetch(`{{ route('admin.comments.index') }}/${id}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
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

function batchAction(action, confirmMessage) {
    const selected = Array.from(document.querySelectorAll('.comment-checkbox:checked')).map(cb => cb.value);
    
    if (selected.length === 0) {
        showMessage('请先选择要操作的评论', 'warning');
        return;
    }
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    fetch(`{{ route('admin.comments.index') }}/bulk`, {
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
.comment-pending {
    background-color: #fef3c7;
}

.comment-pending:hover {
    background-color: #fde68a;
}

.empty-state a {
    color: #667eea;
    text-decoration: none;
}

.empty-state a:hover {
    text-decoration: underline;
}
</style>
@endsection 