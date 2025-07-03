<?php $__env->startSection('content'); ?>
<div class="report-index">


    <?php if(count($reports) > 0): ?>
    <!-- 报告列表 -->
    <div class="report-list">
        <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('reports.show', $report['slug'])); ?>" class="report-card-link">
            <div class="report-card" data-aos="fade-up" data-aos-delay="<?php echo e($loop->index * 100); ?>">
                <!-- 卡片左侧：图标和内容 -->
                <div class="card-main-content">
                    <div class="card-icon">
                        <?php switch(pathinfo($report['slug'], PATHINFO_EXTENSION)):
                            case ('sql'): ?>
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M5,3H19A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3M5,5V19H19V5H5M7,7H17V9H7V7M7,11H17V13H7V11M7,15H17V17H7V15Z"/>
                                </svg>
                                <?php break; ?>
                            <?php case ('xss'): ?>
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V15H11V17M11,13H13V7H11V13Z"/>
                                </svg>
                                <?php break; ?>
                            <?php default: ?>
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                        <?php endswitch; ?>
                    </div>
                    
                    <div class="card-content">
                        <h3 class="report-title"><?php echo e($report['title']); ?></h3>
                        <p class="report-excerpt"><?php echo e($report['excerpt'] ?? '点击查看完整内容...'); ?></p>
                        
                        <div class="report-meta">
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M16.2,16.2L11,13V7H12.5V12.2L17,14.9L16.2,16.2Z"/>
                                </svg>
                                <?php echo e(date('Y-m-d H:i', $report['mtime'])); ?>

                            </span>
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                                <?php echo e(number_format($report['size'] / 1024, 1)); ?> KB
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- 卡片右侧：箭头指示器 -->
                <div class="card-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                    </svg>
                </div>
            </div>
        </a>
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



<?php $__env->startPush('scripts'); ?>
<script>
// 简化的搜索功能
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('report-search');
    const searchResults = document.getElementById('search-results');
    const reportCards = document.querySelectorAll('.report-card-link');
    
    if (searchInput && reportCards.length > 0) {
        // 搜索功能
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            reportCards.forEach(card => {
                const title = card.querySelector('.report-title').textContent.toLowerCase();
                const excerpt = card.querySelector('.report-excerpt').textContent.toLowerCase();
                
                if (searchTerm === '' || title.includes(searchTerm) || excerpt.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // 更新搜索结果显示
            if (searchResults) {
                if (searchTerm === '') {
                    searchResults.style.display = 'none';
                } else {
                    searchResults.style.display = 'block';
                    document.getElementById('results-count').textContent = visibleCount;
                }
            }
        });
        
        // 快捷键支持
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }
    
    // 页面加载动画
    const cards = document.querySelectorAll('.report-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.animation = 'slideInUp 0.6s ease forwards';
        }, index * 100);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/wither-birth/projects/laravel_report_system/resources/views/index.blade.php ENDPATH**/ ?>