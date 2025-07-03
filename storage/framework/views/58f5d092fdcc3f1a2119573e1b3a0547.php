<?php $__env->startSection('content'); ?>
<div class="report-index">
    <!-- 页面标题和统计信息 -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">📚 报告列表</h1>
            <div class="header-actions">
                <a href="<?php echo e(route('reports.create')); ?>" class="btn btn-primary upload-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                    上传报告
                </a>
            </div>
            <div class="stats-info">
                <span class="stat-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                    共 <?php echo e(count($reports)); ?> 个报告
                </span>
                <span class="stat-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M16.2,16.2L11,13V7H12.5V12.2L17,14.9L16.2,16.2Z"/>
                    </svg>
                    最近更新: <?php echo e(count($reports) > 0 ? '最近有更新' : '无'); ?>

                </span>
            </div>
        </div>
    </div>

    <?php if(count($reports) > 3): ?>
    <!-- 增强的搜索框 -->
    <div class="search-section">
        <div class="search-container">
            <div class="search-input-wrapper">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input 
                    type="text" 
                    id="report-search" 
                    placeholder="🔍 搜索报告标题或内容..."
                    class="search-input"
                >
                <div class="search-shortcut">
                    <kbd>Ctrl</kbd> + <kbd>K</kbd>
                </div>
            </div>
            <div id="search-results" class="search-results" style="display: none;">
                找到 <span class="highlight" id="results-count">0</span> 个报告
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(count($reports) > 0): ?>
    <!-- 报告列表 -->
    <div class="report-list">
        <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="report-card" data-aos="fade-up" data-aos-delay="<?php echo e($loop->index * 100); ?>">
            <div class="card-header">
                <div class="card-icon">
                    <?php switch(pathinfo($report['slug'], PATHINFO_EXTENSION)):
                        case ('sql'): ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5,3H19A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3M5,5V19H19V5H5M7,7H17V9H7V7M7,11H17V13H7V11M7,15H17V17H7V15Z"/>
                            </svg>
                            <?php break; ?>
                        <?php case ('xss'): ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V15H11V17M11,13H13V7H11V13Z"/>
                            </svg>
                            <?php break; ?>
                        <?php default: ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                            </svg>
                    <?php endswitch; ?>
                </div>
                <div class="card-status">
                    <span class="status-badge status-<?php echo e($report['status'] ?? 'active'); ?>">
                        <?php echo e($report['status'] ?? 'Active'); ?>

                    </span>
                </div>
            </div>
            
            <div class="card-content">
                <h3 class="report-title">
                    <a href="<?php echo e(route('reports.show', $report['slug'])); ?>"><?php echo e($report['title']); ?></a>
                </h3>
                
                <div class="report-excerpt">
                    <?php echo e($report['excerpt'] ?? '暂无摘要'); ?>

                </div>
                
                <div class="report-meta">
                    <div class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M16.2,16.2L11,13V7H12.5V12.2L17,14.9L16.2,16.2Z"/>
                        </svg>
                        <span><?php echo e(date('Y-m-d H:i', $report['mtime'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        <span><?php echo e(number_format($report['size'] / 1024, 1)); ?> KB</span>
                    </div>
                </div>
            </div>
            
            <div class="card-actions">
                <!-- 主要操作按钮 -->
                <a href="<?php echo e(route('reports.show', $report['slug'])); ?>" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/>
                    </svg>
                    查看完整报告
                </a>
                
                <!-- 次要操作按钮组 -->
                <div class="secondary-actions">
                    <button class="btn btn-secondary share-btn" data-slug="<?php echo e($report['slug']); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18,16.08C17.24,16.08 16.56,16.38 16.04,16.85L8.91,12.7C8.96,12.47 9,12.24 9,12C9,11.76 8.96,11.53 8.91,11.3L15.96,7.19C16.5,7.69 17.21,8 18,8A3,3 0 0,0 21,5A3,3 0 0,0 18,2A3,3 0 0,0 15,5C15,5.24 15.04,5.47 15.09,5.7L8.04,9.81C7.5,9.31 6.79,9 6,9A3,3 0 0,0 3,12A3,3 0 0,0 6,15C6.79,15 7.5,14.69 8.04,14.19L15.16,18.34C15.11,18.55 15.08,18.77 15.08,19C15.08,20.61 16.39,21.91 18,21.91C19.61,21.91 20.92,20.61 20.92,19A2.92,2.92 0 0,0 18,16.08Z"/>
                        </svg>
                        分享
                    </button>
                    <button class="btn btn-danger delete-btn" data-slug="<?php echo e($report['slug']); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/>
                        </svg>
                        删除
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php else: ?>
    <!-- 空状态 -->
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
            </svg>
        </div>
        <h3>📭 暂无报告</h3>
        <p>开始上传你的第一个 Markdown 报告吧！</p>
        <div class="empty-actions">
            <a href="<?php echo e(route('reports.create')); ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M17,13H13V17H11V13H7V11H11V7H13V11H17V13Z"/>
                </svg>
                上传报告
            </a>
        </div>
        <small class="text-muted">支持的文件格式: .md, .txt</small>
    </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.header-actions {
    margin-bottom: 1.5rem;
}

.upload-btn {
    background: var(--gradient-fire);
    color: white;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(241, 39, 17, 0.3);
    animation: pulse-glow 2s ease-in-out infinite;
}

.upload-btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(241, 39, 17, 0.4);
}

@keyframes pulse-glow {
    0%, 100% { box-shadow: 0 4px 15px rgba(241, 39, 17, 0.3); }
    50% { box-shadow: 0 4px 25px rgba(241, 39, 17, 0.5); }
}

.btn-danger {
    background: var(--error-color);
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// 删除报告功能
function deleteReport(slug) {
    if (!confirm('确定要删除这个报告吗？此操作不可撤销！')) {
        return;
    }
    
    // 显示加载状态
    const loadingToast = showToast('🗑️ 正在删除报告...', 'info');
    
    fetch(`/reports/${slug}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideToast(loadingToast);
        
        if (data.message) {
            showToast('✅ ' + data.message, 'success');
            // 刷新页面
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('❌ ' + (data.error || '删除失败'), 'error');
        }
    })
    .catch(error => {
        hideToast(loadingToast);
        showToast('❌ 网络错误，请重试', 'error');
    });
}

// 显示提示消息
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.innerHTML = message;
    
    const colors = {
        info: 'linear-gradient(45deg, #667eea, #764ba2)',
        success: 'linear-gradient(45deg, #10b981, #059669)',
        error: 'linear-gradient(45deg, #ef4444, #dc2626)'
    };
    
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type] || colors.info};
        color: white;
        padding: 15px 25px;
        border-radius: 10px;
        font-weight: 600;
        z-index: 10000;
        animation: slideInRight 0.5s ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        max-width: 300px;
    `;
    
    document.body.appendChild(toast);
    
    // 自动隐藏
    if (type !== 'info') {
        setTimeout(() => hideToast(toast), 3000);
    }
    
    return toast;
}

// 隐藏提示消息
function hideToast(toast) {
    if (toast && toast.parentNode) {
        toast.style.animation = 'slideOutRight 0.5s ease';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 500);
    }
}

// 分享报告功能
function shareReport(slug) {
    const url = `${window.location.origin}/reports/${slug}`;
    
    if (navigator.share) {
        navigator.share({
            title: '查看报告',
            text: '来看看这个有趣的报告',
            url: url
        });
    } else {
        // 复制到剪贴板
        navigator.clipboard.writeText(url).then(() => {
            showToast('🎉 链接已复制到剪贴板！', 'success');
        });
    }
}

// 搜索功能（使用原生JavaScript实现）
document.addEventListener('DOMContentLoaded', function() {
    // 处理分享按钮点击事件
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const slug = this.getAttribute('data-slug');
            shareReport(slug);
        });
    });
    
    // 处理删除按钮点击事件
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const slug = this.getAttribute('data-slug');
            deleteReport(slug);
        });
    });
    
    const searchInput = document.getElementById('report-search');
    const searchResults = document.getElementById('search-results');
    const resultsCount = document.getElementById('results-count');
    const reportCards = document.querySelectorAll('.report-card');
    
    if (searchInput && reportCards.length > 0) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            let visibleCount = 0;
            
            reportCards.forEach(card => {
                const title = card.querySelector('.report-title').textContent.toLowerCase();
                const meta = card.querySelector('.report-meta').textContent.toLowerCase();
                const excerpt = card.querySelector('.report-excerpt').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || meta.includes(searchTerm) || excerpt.includes(searchTerm)) {
                    card.style.display = 'block';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                    visibleCount++;
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        if (card.style.opacity === '0') {
                            card.style.display = 'none';
                        }
                    }, 300);
                }
            });
            
            // 更新搜索结果显示
            if (searchTerm) {
                resultsCount.textContent = visibleCount;
                searchResults.style.display = 'block';
            } else {
                searchResults.style.display = 'none';
                // 重置所有卡片显示
                reportCards.forEach(card => {
                    card.style.display = 'block';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                });
            }
        });
    }
    
    // 模拟AOS动画效果
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '50px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.report-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/wither-birth/projects/laravel_report_system/resources/views/index.blade.php ENDPATH**/ ?>