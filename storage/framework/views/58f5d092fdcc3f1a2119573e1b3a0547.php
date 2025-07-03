<?php $__env->startSection('content'); ?>
<div class="report-index">
    <!-- 页面标题和统计信息 -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">📚 报告列表</h1>
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
                    <a href="<?php echo e(url($report['slug'].'.html')); ?>"><?php echo e($report['title']); ?></a>
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
                <a href="<?php echo e(url($report['slug'].'.html')); ?>" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/>
                    </svg>
                    查看详情
                </a>
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
        <p>将 Markdown 文件放入 <code>storage/reports/</code> 目录即可开始使用</p>
        <div class="empty-actions">
            <button class="btn btn-primary" onclick="location.reload()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.65,6.35C16.2,4.9 14.21,4 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20C15.73,20 18.84,17.45 19.73,14H17.65C16.83,16.33 14.61,18 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6C13.66,6 15.14,6.69 16.22,7.78L13,11H20V4L17.65,6.35Z"/>
                </svg>
                刷新页面
            </button>
        </div>
        <small class="text-muted">支持的文件格式: .md</small>
    </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// 分享报告功能
function shareReport(slug) {
    const url = `${window.location.origin}/${slug}.html`;
    
    if (navigator.share) {
        navigator.share({
            title: '查看报告',
            text: '来看看这个有趣的报告',
            url: url
        });
    } else {
        // 复制到剪贴板
        navigator.clipboard.writeText(url).then(() => {
            alert('链接已复制到剪贴板');
        });
    }
}

// 搜索功能（使用原生JavaScript实现）
document.addEventListener('DOMContentLoaded', function() {
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