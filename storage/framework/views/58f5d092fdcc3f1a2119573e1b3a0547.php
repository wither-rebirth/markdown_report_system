<?php $__env->startSection('content'); ?>
<div class="report-index">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>📚 报告列表</h1>
        <small style="color: var(--secondary-color);">共 <?php echo e(count($reports)); ?> 个报告</small>
    </div>

    <?php if(count($reports) > 3): ?>
    <!-- 搜索框 -->
    <div style="margin-bottom: 2rem;">
        <input type="text" 
               id="report-search" 
               placeholder="🔍 搜索报告标题或内容..." 
               style="width: 100%;">
    </div>
    <?php endif; ?>

    <?php if(count($reports) > 0): ?>
    <!-- 报告列表 -->
    <div class="report-list">
        <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="report-card">
            <h3 class="report-title">
                <a href="<?php echo e(url($report['slug'].'.html')); ?>"><?php echo e($report['title']); ?></a>
            </h3>
            <div class="report-meta">
                📅 更新时间: <?php echo e(date('Y年m月d日 H:i', $report['mtime'])); ?> | 
                📄 大小: <?php echo e(number_format($report['size'] / 1024, 1)); ?> KB |
                🔗 <a href="<?php echo e(url($report['slug'].'.html')); ?>">查看详情</a>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php else: ?>
    <!-- 空状态 -->
    <div style="text-align: center; padding: 3rem; color: var(--secondary-color);">
        <h3>📭 暂无报告</h3>
        <p>将 Markdown 文件放入 <code>storage/reports/</code> 目录即可开始使用</p>
        <small>支持的文件格式: .md</small>
    </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// 如果URL中有hash，平滑滚动到对应元素
if (window.location.hash) {
    setTimeout(() => {
        const target = document.querySelector(window.location.hash);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    }, 100);
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/wither-birth/projects/laravel_report_system/resources/views/index.blade.php ENDPATH**/ ?>