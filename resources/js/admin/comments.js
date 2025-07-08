// Comments 模块 JavaScript 功能

// 导入基础功能
import { showMessage } from './categories.js';

// 全选功能
export function initSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    if (!selectAllCheckbox) return;
    
    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.comment-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            updateRowSelection(checkbox);
        });
        updateBatchActions();
    });
}

// 单个选择框功能
export function initIndividualSelect() {
    document.querySelectorAll('.comment-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateRowSelection(this);
            updateBatchActions();
            updateSelectAllState();
        });
    });
}

// 更新行选择状态
function updateRowSelection(checkbox) {
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        row.classList.add('selected');
    } else {
        row.classList.remove('selected');
    }
}

// 更新全选框状态
function updateSelectAllState() {
    const selectAllCheckbox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.comment-checkbox');
    const checkedBoxes = document.querySelectorAll('.comment-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedBoxes.length === checkboxes.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    }
}

// 更新批量操作按钮显示
export function updateBatchActions() {
    const selected = document.querySelectorAll('.comment-checkbox:checked');
    const batchButtons = document.querySelectorAll('#batch-approve, #batch-spam, #batch-delete');
    
    if (selected.length > 0) {
        batchButtons.forEach(btn => btn.classList.add('show'));
        showSelectionHint(selected.length);
    } else {
        batchButtons.forEach(btn => btn.classList.remove('show'));
        hideSelectionHint();
    }
}

// 显示选择提示
function showSelectionHint(count) {
    let hint = document.querySelector('.selection-hint');
    if (!hint) {
        hint = document.createElement('div');
        hint.className = 'selection-hint';
        hint.innerHTML = `已选择 <span class="selection-count">${count}</span> 条评论`;
        document.body.appendChild(hint);
    } else {
        hint.querySelector('.selection-count').textContent = count;
    }
    
    setTimeout(() => hint.classList.add('show'), 10);
}

// 隐藏选择提示
function hideSelectionHint() {
    const hint = document.querySelector('.selection-hint');
    if (hint) {
        hint.classList.remove('show');
        setTimeout(() => {
            if (hint.parentNode) {
                hint.parentNode.removeChild(hint);
            }
        }, 300);
    }
}

// 批量通过功能
export function initBatchApprove() {
    const batchApproveBtn = document.getElementById('batch-approve');
    if (!batchApproveBtn) return;
    
    batchApproveBtn.addEventListener('click', function() {
        const selected = getSelectedCommentIds();
        if (selected.length === 0) return;
        
        if (!confirm(`确定要通过选中的 ${selected.length} 条评论吗？`)) return;
        
        performBatchAction('approve', selected);
    });
}

// 批量标记垃圾功能
export function initBatchSpam() {
    const batchSpamBtn = document.getElementById('batch-spam');
    if (!batchSpamBtn) return;
    
    batchSpamBtn.addEventListener('click', function() {
        const selected = getSelectedCommentIds();
        if (selected.length === 0) return;
        
        if (!confirm(`确定要将选中的 ${selected.length} 条评论标记为垃圾吗？`)) return;
        
        performBatchAction('spam', selected);
    });
}

// 批量删除功能
export function initBatchDelete() {
    const batchDeleteBtn = document.getElementById('batch-delete');
    if (!batchDeleteBtn) return;
    
    batchDeleteBtn.addEventListener('click', function() {
        const selected = getSelectedCommentIds();
        if (selected.length === 0) return;
        
        if (!confirm(`确定要删除选中的 ${selected.length} 条评论吗？此操作不可恢复！`)) return;
        
        performBatchAction('delete', selected);
    });
}

// 获取选中的评论ID
function getSelectedCommentIds() {
    const selected = document.querySelectorAll('.comment-checkbox:checked');
    return Array.from(selected).map(checkbox => checkbox.value);
}

// 执行批量操作
function performBatchAction(action, commentIds) {
    showLoadingOverlay();
    
    fetch(`/admin/comments/batch-${action}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ comment_ids: commentIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message || '操作成功', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(data.message || '操作失败', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('网络错误', 'error');
    })
    .finally(() => {
        hideLoadingOverlay();
    });
}

// 显示加载覆盖层
function showLoadingOverlay() {
    let overlay = document.querySelector('.loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(overlay);
    }
    overlay.classList.add('show');
}

// 隐藏加载覆盖层
function hideLoadingOverlay() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.classList.remove('show');
    }
}

// 单个评论通过功能
export function initIndividualApprove() {
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.dataset.id;
            const row = this.closest('tr');
            
            this.disabled = true;
            this.style.opacity = '0.6';
            
            fetch(`/admin/comments/${commentId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('评论已通过', 'success');
                    
                    // 更新状态显示
                    const statusBadge = row.querySelector('.status-badge');
                    if (statusBadge) {
                        statusBadge.textContent = '已通过';
                        statusBadge.className = 'status-badge active';
                    }
                    
                    // 移除待审核样式
                    row.classList.remove('comment-pending');
                    
                    // 隐藏通过按钮
                    this.style.display = 'none';
                } else {
                    showMessage('操作失败', 'error');
                }
            })
            .catch(error => {
                showMessage('网络错误', 'error');
            })
            .finally(() => {
                this.disabled = false;
                this.style.opacity = '1';
            });
        });
    });
}

// 单个评论标记垃圾功能
export function initIndividualSpam() {
    document.querySelectorAll('.spam-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const commentId = this.dataset.id;
            const row = this.closest('tr');
            const self = this;
            
            // 使用自定义确认对话框
            import('./confirm-dialog.js').then(module => {
                module.showConfirmDialog('确定要将此评论标记为垃圾吗？', function() {
                    performSpamAction(self, commentId, row);
                });
            });
        });
    });
}

// 执行垃圾评论标记操作
function performSpamAction(button, commentId, row) {
    button.disabled = true;
    button.style.opacity = '0.6';
    
    fetch(`/admin/comments/${commentId}/spam`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('已标记为垃圾评论', 'success');
            
            // 更新状态显示
            const statusBadge = row.querySelector('.status-badge');
            if (statusBadge) {
                statusBadge.textContent = '垃圾';
                statusBadge.className = 'status-badge danger';
            }
            
            // 隐藏垃圾按钮
            button.style.display = 'none';
        } else {
            showMessage('操作失败', 'error');
        }
    })
    .catch(error => {
        showMessage('网络错误', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.style.opacity = '1';
    });
}

// 删除确认功能 - 已移至 confirm-dialog.js
export function initDeleteConfirmation() {
    // 不再需要，确认对话框由 confirm-dialog.js 处理
}

// 评论内容展开/收起
export function initContentToggle() {
    document.querySelectorAll('.comment-content').forEach(content => {
        if (content.scrollHeight > content.clientHeight) {
            content.style.cursor = 'pointer';
            content.title = '点击展开/收起';
            
            let isExpanded = false;
            
            content.addEventListener('click', function() {
                if (isExpanded) {
                    this.style.maxHeight = '80px';
                    this.style.overflow = 'hidden';
                } else {
                    this.style.maxHeight = 'none';
                    this.style.overflow = 'visible';
                }
                isExpanded = !isExpanded;
            });
        }
    });
}

// 快捷键支持
export function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + A: 全选
        if ((e.ctrlKey || e.metaKey) && e.key === 'a' && e.target.tagName !== 'INPUT') {
            e.preventDefault();
            const selectAll = document.getElementById('select-all');
            if (selectAll) {
                selectAll.checked = !selectAll.checked;
                selectAll.dispatchEvent(new Event('change'));
            }
        }
        
        // Delete: 删除选中项
        if (e.key === 'Delete') {
            const selected = document.querySelectorAll('.comment-checkbox:checked');
            if (selected.length > 0) {
                const deleteBtn = document.getElementById('batch-delete');
                if (deleteBtn && deleteBtn.classList.contains('show')) {
                    deleteBtn.click();
                }
            }
        }
        
        // Escape: 取消选择
        if (e.key === 'Escape') {
            document.querySelectorAll('.comment-checkbox:checked').forEach(checkbox => {
                checkbox.checked = false;
                updateRowSelection(checkbox);
            });
            updateBatchActions();
            updateSelectAllState();
        }
    });
}

// 自动刷新功能（可选）
export function initAutoRefresh() {
    const refreshInterval = 60000; // 60秒
    
    setInterval(() => {
        // 只有在没有选中项时才自动刷新
        const selected = document.querySelectorAll('.comment-checkbox:checked');
        if (selected.length === 0) {
            const currentUrl = new URL(window.location);
            fetch(currentUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                // 检查是否有新评论
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newTableBody = tempDiv.querySelector('.data-table tbody');
                const currentTableBody = document.querySelector('.data-table tbody');
                
                if (newTableBody && currentTableBody) {
                    const newRowCount = newTableBody.children.length;
                    const currentRowCount = currentTableBody.children.length;
                    
                    if (newRowCount > currentRowCount) {
                        showMessage(`有 ${newRowCount - currentRowCount} 条新评论`, 'info');
                    }
                }
            })
            .catch(error => {
                console.log('Auto refresh failed:', error);
            });
        }
    }, refreshInterval);
}

// 初始化所有comments模块功能
export function initCommentsModule() {
    initSelectAll();
    initIndividualSelect();
    initBatchApprove();
    initBatchSpam();
    initBatchDelete();
    initIndividualApprove();
    initIndividualSpam();
    initDeleteConfirmation();
    initContentToggle();
    initKeyboardShortcuts();
    
    // 可选功能
    // initAutoRefresh();
}

// 将函数暴露到全局作用域（用于兼容性）
if (typeof window !== 'undefined') {
    window.updateBatchActions = updateBatchActions;
}

// 页面加载完成后自动初始化
document.addEventListener('DOMContentLoaded', function() {
    initCommentsModule();
}); 