// Backup 模块 JavaScript 功能

// 显示创建备份模态框
export function showCreateBackupModal() {
    document.getElementById('createBackupModal').style.display = 'block';
}

// 显示清理备份模态框
export function showCleanupModal() {
    document.getElementById('cleanupModal').style.display = 'block';
}

// 关闭模态框
export function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// 创建备份
export function createBackup(type) {
    const button = event.target;
    const originalText = button.textContent;
    
    button.textContent = '创建中...';
    button.disabled = true;
    
    let url = '';
    switch(type) {
        case 'database':
            url = '/admin/backup/database';
            break;
        case 'files':
            url = '/admin/backup/files';
            break;
        case 'full':
            url = '/admin/backup/full';
            break;
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('备份创建成功！文件：' + data.filename);
            location.reload();
        } else {
            alert('备份创建失败：' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('备份创建失败：网络错误');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

// 删除备份
export function deleteBackup(filename) {
    if (!confirm('确定要删除备份文件 "' + filename + '" 吗？此操作不可恢复。')) {
        return;
    }
    
    fetch(`/admin/backup/delete/${filename}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('备份文件删除成功');
            location.reload();
        } else {
            alert('删除失败：' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('删除失败：网络错误');
    });
}

// 清理旧备份
export function cleanupBackups() {
    const days = document.querySelector('input[name="cleanup_days"]:checked').value;
    
    if (!confirm(`确定要删除所有 ${days} 天前的备份文件吗？`)) {
        return;
    }
    
    fetch('/admin/backup/cleanup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            days: parseInt(days)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('清理失败：' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('清理失败：网络错误');
    });
}

// 初始化模态框事件
export function initModalEvents() {
    // 点击模态框外部关闭
    window.onclick = function(event) {
        const modals = document.getElementsByClassName('modal');
        for (let modal of modals) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    }
}

// 初始化备份按钮动画
export function initBackupButtons() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(function(button) {
        button.addEventListener('mouseenter', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(-1px)';
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
}

// 初始化统计卡片动画
export function initStatCardAnimation() {
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        });
    });
}

// 初始化表格排序
export function initTableSorting() {
    const table = document.querySelector('.table');
    if (!table) return;
    
    const headers = table.querySelectorAll('th');
    
    headers.forEach(function(header, index) {
        // 跳过操作列
        if (header.textContent.includes('操作')) return;
        
        header.style.cursor = 'pointer';
        header.style.userSelect = 'none';
        
        header.addEventListener('click', function() {
            sortTableByColumn(table, index);
        });
        
        // 添加排序指示器
        header.addEventListener('mouseenter', function() {
            if (!this.querySelector('.sort-indicator')) {
                const indicator = document.createElement('span');
                indicator.className = 'sort-indicator';
                indicator.textContent = ' ↕';
                indicator.style.opacity = '0.5';
                this.appendChild(indicator);
            }
        });
    });
}

function sortTableByColumn(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const isNumeric = rows.some(row => {
        const cell = row.cells[columnIndex];
        const text = cell?.textContent.trim().replace(/[,\s]/g, '') || '';
        return !isNaN(parseFloat(text)) && isFinite(text);
    });
    
    rows.sort((a, b) => {
        const aVal = a.cells[columnIndex]?.textContent.trim() || '';
        const bVal = b.cells[columnIndex]?.textContent.trim() || '';
        
        if (isNumeric) {
            return parseFloat(bVal.replace(/[,\s]/g, '')) - parseFloat(aVal.replace(/[,\s]/g, ''));
        } else {
            return bVal.localeCompare(aVal, 'zh-CN');
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// 初始化文件大小格式化
export function initFileSizeFormatting() {
    const sizeElements = document.querySelectorAll('[data-size]');
    
    sizeElements.forEach(function(element) {
        const size = parseInt(element.dataset.size);
        element.textContent = formatFileSize(size);
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// 初始化进度指示器
export function initProgressIndicators() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach(function(bar) {
        const percentage = bar.dataset.percentage || 0;
        bar.style.width = percentage + '%';
        bar.style.transition = 'width 0.3s ease';
    });
}

// 初始化所有backup模块功能
export function initBackupModule() {
    initModalEvents();
    initBackupButtons();
    initStatCardAnimation();
    initTableSorting();
    initFileSizeFormatting();
    initProgressIndicators();
}

// 将函数暴露到全局作用域（用于内联事件处理器）
if (typeof window !== 'undefined') {
    window.showCreateBackupModal = showCreateBackupModal;
    window.showCleanupModal = showCleanupModal;
    window.closeModal = closeModal;
    window.createBackup = createBackup;
    window.deleteBackup = deleteBackup;
    window.cleanupBackups = cleanupBackups;
}

// 页面加载完成后自动初始化
document.addEventListener('DOMContentLoaded', function() {
    initBackupModule();
}); 