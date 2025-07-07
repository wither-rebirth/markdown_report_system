// 管理端JavaScript功能

document.addEventListener('DOMContentLoaded', function() {
    // 侧边栏切换功能
    initSidebarToggle();
    
    // 表格功能
    initTableFeatures();
    
    // 表单功能
    initFormFeatures();
    
    // 确认对话框
    initConfirmDialogs();
    
    // 自动消失的消息提示
    initAlerts();
    
    // CSRF Token 设置
    setupCSRF();
});

// 侧边栏切换
function initSidebarToggle() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
        });
        
        // 点击内容区域关闭侧边栏
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        });
    }
}

// 表格功能
function initTableFeatures() {
    // 全选/取消全选
    const selectAllCheckbox = document.querySelector('input[data-toggle="select-all"]');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[data-toggle="select-item"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }
    
    // 单选框变化时更新全选状态
    const itemCheckboxes = document.querySelectorAll('input[data-toggle="select-item"]');
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateBulkActions();
        });
    });
    
    // 排序功能
    initTableSorting();
}

// 更新全选状态
function updateSelectAllState() {
    const selectAllCheckbox = document.querySelector('input[data-toggle="select-all"]');
    const itemCheckboxes = document.querySelectorAll('input[data-toggle="select-item"]');
    
    if (selectAllCheckbox && itemCheckboxes.length > 0) {
        const checkedCount = Array.from(itemCheckboxes).filter(cb => cb.checked).length;
        
        if (checkedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount === itemCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }
}

// 更新批量操作按钮状态
function updateBulkActions() {
    const checkedItems = document.querySelectorAll('input[data-toggle="select-item"]:checked');
    const bulkActionButtons = document.querySelectorAll('.bulk-action-btn');
    
    if (checkedItems.length > 0) {
        bulkActionButtons.forEach(btn => btn.disabled = false);
    } else {
        bulkActionButtons.forEach(btn => btn.disabled = true);
    }
}

// 表格排序
function initTableSorting() {
    const sortableHeaders = document.querySelectorAll('[data-sort]');
    
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            const currentDirection = this.dataset.direction || 'asc';
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            
            // 更新URL参数并重新加载
            const url = new URL(window.location);
            url.searchParams.set('sort', column);
            url.searchParams.set('direction', newDirection);
            window.location.href = url.toString();
        });
    });
}

// 表单功能
function initFormFeatures() {
    // 自动生成slug
    const titleInput = document.querySelector('input[name="title"]');
    const slugInput = document.querySelector('input[name="slug"]');
    
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugInput.dataset.userModified) {
                slugInput.value = generateSlug(this.value);
            }
        });
        
        slugInput.addEventListener('input', function() {
            this.dataset.userModified = 'true';
        });
    }
    
    // 字符计数器
    const textareas = document.querySelectorAll('textarea[data-max-length]');
    textareas.forEach(textarea => {
        const maxLength = parseInt(textarea.dataset.maxLength);
        const counter = document.createElement('div');
        counter.className = 'form-help text-right';
        counter.style.marginTop = '0.5rem';
        textarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${textarea.value.length}/${maxLength} 字符`;
            
            if (remaining < 0) {
                counter.style.color = '#ef4444';
            } else if (remaining < 50) {
                counter.style.color = '#f59e0b';
            } else {
                counter.style.color = '#6b7280';
            }
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
    
    // 颜色选择器预览
    const colorInputs = document.querySelectorAll('input[type="color"]');
    colorInputs.forEach(input => {
        const preview = document.createElement('div');
        preview.style.cssText = `
            width: 2rem;
            height: 2rem;
            border-radius: 0.25rem;
            border: 1px solid #d1d5db;
            margin-left: 0.5rem;
            display: inline-block;
            vertical-align: middle;
        `;
        input.parentNode.appendChild(preview);
        
        function updatePreview() {
            preview.style.backgroundColor = input.value;
        }
        
        input.addEventListener('input', updatePreview);
        updatePreview();
    });
}

// 生成URL友好的slug
function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '') // 移除特殊字符
        .replace(/[\s_-]+/g, '-') // 替换空格和下划线为连字符
        .replace(/^-+|-+$/g, ''); // 移除开头和结尾的连字符
}

// 确认对话框
function initConfirmDialogs() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || '确定要执行此操作吗？';
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// 自动消失的消息提示
function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        // 添加关闭按钮
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            margin-left: auto;
            color: inherit;
            opacity: 0.7;
        `;
        
        closeBtn.addEventListener('click', function() {
            alert.style.transition = 'opacity 0.3s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
        
        alert.appendChild(closeBtn);
        
        // 自动消失（仅成功消息）
        if (alert.classList.contains('alert-success')) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }
    });
}

// CSRF Token 设置
function setupCSRF() {
    const token = document.querySelector('meta[name="csrf-token"]');
    
    if (token) {
        // 为所有AJAX请求设置CSRF token
        const originalFetch = window.fetch;
        window.fetch = function(url, options = {}) {
            if (options.method && options.method.toUpperCase() !== 'GET') {
                options.headers = options.headers || {};
                options.headers['X-CSRF-TOKEN'] = token.getAttribute('content');
            }
            return originalFetch(url, options);
        };
        
        // 为jQuery AJAX设置（如果使用）
        if (window.jQuery) {
            jQuery.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token.getAttribute('content')
                }
            });
        }
    }
}

// AJAX请求辅助函数
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    };
    
    return fetch(url, { ...defaultOptions, ...options })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

// 显示加载状态
function showLoading(element) {
    if (element) {
        element.disabled = true;
        const originalText = element.textContent;
        element.dataset.originalText = originalText;
        element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 加载中...';
    }
}

function hideLoading(element) {
    if (element && element.dataset.originalText) {
        element.disabled = false;
        element.textContent = element.dataset.originalText;
        delete element.dataset.originalText;
    }
}

// 显示通知消息
function showNotification(message, type = 'success') {
    // 检查是否有相同的消息正在显示，避免重复
    const existingNotifications = document.querySelectorAll('.alert');
    for (let existing of existingNotifications) {
        if (existing.textContent.trim().includes(message)) {
            return; // 不显示重复消息
        }
    }
    
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: ${1 + (existingNotifications.length * 5)}rem;
        right: 1rem;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    `;
    
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    // 自动消失
    setTimeout(() => {
        notification.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notification.remove();
            // 重新调整其他通知的位置
            repositionNotifications();
        }, 300);
    }, 3000);
}

// 重新调整通知位置
function repositionNotifications() {
    const notifications = document.querySelectorAll('.alert');
    notifications.forEach((notification, index) => {
        notification.style.top = `${1 + (index * 5)}rem`;
    });
}

// 批量操作处理
function handleBulkAction(action, selectedIds) {
    if (selectedIds.length === 0) {
        showNotification('请选择要操作的项目', 'error');
        return;
    }
    
    let confirmMessage = '确定要执行此批量操作吗？';
    
    switch (action) {
        case 'delete':
            confirmMessage = `确定要删除选中的 ${selectedIds.length} 个项目吗？此操作不可撤销。`;
            break;
        case 'approve':
            confirmMessage = `确定要批量审核通过 ${selectedIds.length} 个项目吗？`;
            break;
        case 'reject':
            confirmMessage = `确定要批量拒绝 ${selectedIds.length} 个项目吗？`;
            break;
    }
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // 这里可以发送AJAX请求处理批量操作
    console.log(`执行批量操作: ${action}`, selectedIds);
}

// 导出常用函数供全局使用
window.AdminUtils = {
    makeRequest,
    showLoading,
    hideLoading,
    showNotification,
    handleBulkAction,
    generateSlug
};

// 全局函数别名，保持向后兼容
window.showMessage = (message, type) => {
    showNotification(message, type);
}; 