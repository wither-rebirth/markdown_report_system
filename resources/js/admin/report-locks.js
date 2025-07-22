/**
 * Report Locks Admin JavaScript
 * Handles form interactions, bulk operations, and AJAX requests for admin pages
 */

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeAdminFeatures();
});

/**
 * Initialize all admin features
 */
function initializeAdminFeatures() {
    // Initialize create/edit page features
    if (document.querySelector('.report-item')) {
        initializeReportSelection();
    }
    
    // Initialize index page features
    if (document.querySelector('.bulk-actions')) {
        initializeIndexPageFeatures();
    }
}

/**
 * Report Selection Features (for create page)
 */
function initializeReportSelection() {
    // Handle report item clicks
    document.querySelectorAll('.report-item').forEach(function(item) {
        item.addEventListener('click', function() {
            const slug = this.dataset.slug;
            const title = this.dataset.title;
            const label = this.dataset.label;
            
            selectReport(slug, title, label);
        });
    });
    
    // Handle radio button changes
    document.querySelectorAll('input[name="selected_report"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const reportItem = this.closest('.report-item');
                const slug = this.value;
                const title = reportItem.dataset.title;
                const label = reportItem.dataset.label;
                
                selectReport(slug, title, label);
            }
        });
    });
}

/**
 * Select a report and update form fields
 */
function selectReport(slug, title, label) {
    // Update form fields
    const slugInput = document.querySelector('input[name="slug"]');
    const titleInput = document.querySelector('input[name="title"]');
    const labelSelect = document.querySelector('select[name="label"]');
    
    if (slugInput) slugInput.value = slug;
    if (titleInput) titleInput.value = title;
    if (labelSelect) labelSelect.value = label;
    
    // Update radio button selection
    document.querySelectorAll('input[name="selected_report"]').forEach(function(radio) {
        radio.checked = radio.value === slug;
    });
}

/**
 * Index Page Features
 */
function initializeIndexPageFeatures() {
    initializeCheckboxes();
    initializeBulkActions();
    initializeActionButtons();
}

/**
 * Initialize checkbox functionality
 */
function initializeCheckboxes() {
    const selectAllMain = document.getElementById('select-all');
    const selectAllHeader = document.getElementById('select-all-header');
    const itemCheckboxes = document.querySelectorAll('.select-item');
    
    // Main select all functionality
    if (selectAllMain) {
        selectAllMain.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            if (selectAllHeader) {
                selectAllHeader.checked = this.checked;
            }
        });
    }
    
    // Header select all functionality
    if (selectAllHeader) {
        selectAllHeader.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            if (selectAllMain) {
                selectAllMain.checked = this.checked;
            }
        });
    }
    
    // Update select-all state when individual items change
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
            const noneChecked = Array.from(itemCheckboxes).every(cb => !cb.checked);
            
            if (selectAllMain) {
                selectAllMain.checked = allChecked;
                selectAllMain.indeterminate = !allChecked && !noneChecked;
            }
            
            if (selectAllHeader) {
                selectAllHeader.checked = allChecked;
                selectAllHeader.indeterminate = !allChecked && !noneChecked;
            }
        });
    });
}

/**
 * Initialize bulk actions
 */
function initializeBulkActions() {
    const bulkActionButton = document.getElementById('apply-bulk-action');
    
    if (bulkActionButton) {
        bulkActionButton.addEventListener('click', function() {
            const action = document.getElementById('bulk-action').value;
            const selectedIds = Array.from(document.querySelectorAll('.select-item:checked'))
                .map(cb => cb.value);
            
            if (!action) {
                alert('请选择操作类型');
                return;
            }
            
            if (selectedIds.length === 0) {
                alert('请选择要操作的项目');
                return;
            }
            
            executeBulkAction(action, selectedIds);
        });
    }
}

/**
 * Execute bulk action
 */
function executeBulkAction(action, selectedIds) {
    const actionNames = {
        'enable': '启用',
        'disable': '禁用', 
        'delete': '删除'
    };
    
    const actionName = actionNames[action] || action;
    const confirmMessage = `确定要${actionName} ${selectedIds.length} 个锁定吗？`;
    
    if (confirm(confirmMessage)) {
        // Show loading state
        const button = document.getElementById('apply-bulk-action');
        const originalText = button.textContent;
        button.textContent = '处理中...';
        button.disabled = true;
        
        fetch('/admin/report-locks/bulk-action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken()
            },
            body: JSON.stringify({
                action: action,
                ids: selectedIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('操作失败: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('操作失败，请重试');
        })
        .finally(() => {
            // Restore button state
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}

/**
 * Initialize action buttons (toggle, delete)
 */
function initializeActionButtons() {
    // Toggle status buttons
    document.querySelectorAll('.btn-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.lockId;
            toggleStatus(id);
        });
    });
    
    // Delete buttons
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.lockId;
            const title = this.dataset.lockTitle;
            deleteLock(id, title);
        });
    });
}

/**
 * Toggle lock status
 */
function toggleStatus(id) {
    const button = document.querySelector(`.btn-toggle[data-lock-id="${id}"]`);
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '⏳';
    button.disabled = true;
    
    fetch(`/admin/report-locks/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken()
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('操作失败: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('操作失败，请重试');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

/**
 * Delete lock
 */
function deleteLock(id, title) {
    const confirmMessage = `确定要删除锁定 "${title}" 吗？此操作不可恢复。`;
    
    if (confirm(confirmMessage)) {
        const button = document.querySelector(`.btn-delete[data-lock-id="${id}"]`);
        const originalContent = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '⏳';
        button.disabled = true;
        
        fetch(`/admin/report-locks/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': getCSRFToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('删除失败: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('删除失败，请重试');
        })
        .finally(() => {
            // Restore button state if still exists
            if (button.parentNode) {
                button.innerHTML = originalContent;
                button.disabled = false;
            }
        });
    }
}

/**
 * Get CSRF token from meta tag
 */
function getCSRFToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

/**
 * Utility function to show loading state on buttons
 */
function setButtonLoading(button, loading = true) {
    if (loading) {
        button.dataset.originalText = button.textContent;
        button.textContent = '处理中...';
        button.disabled = true;
    } else {
        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
        delete button.dataset.originalText;
    }
}

/**
 * Utility function to display notifications
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    
    // Insert at top of container
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(notification, container.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
} 