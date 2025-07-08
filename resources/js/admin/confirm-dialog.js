// 自定义确认对话框
export function createConfirmDialog() {
    // 创建对话框HTML
    const dialogHTML = `
        <div id="confirm-dialog" class="confirm-dialog-overlay" style="display: none;">
            <div class="confirm-dialog">
                <div class="confirm-dialog-header">
                    <h3 class="confirm-dialog-title">确认操作</h3>
                </div>
                <div class="confirm-dialog-body">
                    <p id="confirm-dialog-message"></p>
                </div>
                <div class="confirm-dialog-footer">
                    <button id="confirm-dialog-cancel" class="btn btn-secondary">取消</button>
                    <button id="confirm-dialog-confirm" class="btn btn-danger">确认删除</button>
                </div>
            </div>
        </div>
    `;
    
    // 添加CSS样式
    const styles = `
        <style>
        .confirm-dialog-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex !important;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .confirm-dialog-overlay.show {
            opacity: 1;
        }
        
        .confirm-dialog {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            min-width: 400px;
            max-width: 500px;
            transform: scale(0.9);
            transition: transform 0.2s ease;
        }
        
        .confirm-dialog-overlay.show .confirm-dialog {
            transform: scale(1);
        }
        
        .confirm-dialog-header {
            padding: 1.5rem 1.5rem 0;
        }
        
        .confirm-dialog-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: #dc2626;
        }
        
        .confirm-dialog-body {
            padding: 1rem 1.5rem;
        }
        
        .confirm-dialog-body p {
            margin: 0;
            color: #374151;
            line-height: 1.5;
        }
        
        .confirm-dialog-footer {
            padding: 0 1.5rem 1.5rem;
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }
        
        .confirm-dialog-footer .btn {
            min-width: 80px;
        }
        </style>
    `;
    
    // 将样式和对话框添加到页面
    if (!document.getElementById('confirm-dialog')) {
        document.head.insertAdjacentHTML('beforeend', styles);
        document.body.insertAdjacentHTML('beforeend', dialogHTML);
    }
}

// 显示确认对话框
export function showConfirmDialog(message, onConfirm) {
    // 确保对话框已创建
    createConfirmDialog();
    
    const overlay = document.getElementById('confirm-dialog');
    const messageEl = document.getElementById('confirm-dialog-message');
    const cancelBtn = document.getElementById('confirm-dialog-cancel');
    const confirmBtn = document.getElementById('confirm-dialog-confirm');
    
    // 设置消息
    messageEl.textContent = message;
    
    // 显示对话框
    overlay.style.display = 'flex';
    setTimeout(() => overlay.classList.add('show'), 10);
    
    // 清除之前的事件监听器
    const newCancelBtn = cancelBtn.cloneNode(true);
    const newConfirmBtn = confirmBtn.cloneNode(true);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // 绑定事件
    function hideDialog() {
        overlay.classList.remove('show');
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 200);
    }
    
    // 取消按钮
    newCancelBtn.addEventListener('click', hideDialog);
    
    // 确认按钮
    newConfirmBtn.addEventListener('click', function() {
        hideDialog();
        if (onConfirm) onConfirm();
    });
    
    // 点击遮罩层关闭
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            hideDialog();
        }
    });
    
    // ESC键关闭
    function handleEscape(e) {
        if (e.key === 'Escape') {
            hideDialog();
            document.removeEventListener('keydown', handleEscape);
        }
    }
    document.addEventListener('keydown', handleEscape);
}

// 为具有data-confirm属性的元素添加确认对话框
export function initConfirmDialog() {
    document.querySelectorAll('[data-confirm]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const message = this.dataset.confirm;
            const form = this.closest('form');
            
            showConfirmDialog(message, function() {
                if (form) {
                    // 移除data-confirm属性避免重复确认
                    element.removeAttribute('data-confirm');
                    // 提交表单
                    form.submit();
                } else if (element.href) {
                    // 如果是链接，跳转
                    window.location.href = element.href;
                }
            });
        });
    });
}

// 页面加载时自动初始化
document.addEventListener('DOMContentLoaded', function() {
    initConfirmDialog();
}); 