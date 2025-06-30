<?php $__env->startSection('content'); ?>
<div class="report-page">
    <!-- 报告头部信息 -->
    <div class="report-header" style="margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #e2e8f0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h1 style="margin: 0; color: var(--primary-color);"><?php echo e($title); ?></h1>
            <div class="no-print">
                <button onclick="window.print()" style="margin-right: 0.5rem;" title="打印报告">🖨️</button>
                <button onclick="toggleFullscreen()" title="全屏模式">🔍</button>
            </div>
        </div>
        
        <div class="report-meta">
            📅 更新时间: <?php echo e(date('Y年m月d日 H:i', $mtime)); ?> | 
            📄 大小: <?php echo e(number_format($size / 1024, 1)); ?> KB | 
            🔗 <a href="<?php echo e(url('/')); ?>">返回列表</a>
        </div>
    </div>

    <!-- 目录 (如果内容较长) -->
    <div id="table-of-contents" class="no-print" style="margin-bottom: 2rem;"></div>

    <!-- 报告内容 -->
    <article class="report-content">
        <?php echo $html; ?>

    </article>

    <!-- 返回顶部按钮 -->
    <div class="no-print" style="position: fixed; bottom: 2rem; right: 2rem;">
        <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
                style="background: var(--primary-color); color: white; border: none; padding: 0.75rem; border-radius: 50%; box-shadow: 0 2px 10px rgba(0,0,0,0.2);"
                title="返回顶部">
            ⬆️
        </button>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
/* 目录样式 */
.toc-list {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    list-style: none;
}

.toc-list li {
    margin: 0.25rem 0;
}

.toc-h1 { font-weight: bold; }
.toc-h2 { margin-left: 1rem; }
.toc-h3 { margin-left: 2rem; }
.toc-h4 { margin-left: 3rem; }
.toc-h5 { margin-left: 4rem; }
.toc-h6 { margin-left: 5rem; }

/* 报告内容样式优化 */
.report-content {
    font-size: 1rem;
    line-height: 1.7;
}

.report-content h1,
.report-content h2,
.report-content h3,
.report-content h4,
.report-content h5,
.report-content h6 {
    position: relative;
    scroll-margin-top: 2rem;
}

.report-content h1:hover::before,
.report-content h2:hover::before,
.report-content h3:hover::before,
.report-content h4:hover::before,
.report-content h5:hover::before,
.report-content h6:hover::before {
    content: "🔗";
    position: absolute;
    left: -1.5rem;
    color: var(--primary-color);
    text-decoration: none;
}

/* 表格样式 */
.report-content table {
    margin: 1.5rem 0;
    border-collapse: collapse;
    width: 100%;
}

.report-content th,
.report-content td {
    border: 1px solid #e2e8f0;
    padding: 0.75rem;
    text-align: left;
}

.report-content th {
    background-color: #f8fafc;
    font-weight: 600;
}

/* 引用块样式 */
.report-content blockquote {
    border-left: 4px solid var(--primary-color);
    margin: 1.5rem 0;
    padding: 1rem 1.5rem;
    background-color: #f8fafc;
    border-radius: 0 0.375rem 0.375rem 0;
}

/* 列表样式 */
.report-content ul,
.report-content ol {
    margin: 1rem 0;
    padding-left: 2rem;
}

.report-content li {
    margin: 0.5rem 0;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// 为标题添加锚点链接
document.addEventListener('DOMContentLoaded', function() {
    const headings = document.querySelectorAll('.report-content h1, .report-content h2, .report-content h3, .report-content h4, .report-content h5, .report-content h6');
    
    headings.forEach((heading, index) => {
        if (!heading.id) {
            heading.id = 'heading-' + index;
        }
        
        heading.addEventListener('click', function() {
            if (heading.id) {
                window.location.hash = '#' + heading.id;
                navigator.clipboard.writeText(window.location.href).then(() => {
                    console.log('链接已复制到剪贴板');
                });
            }
        });
        
        heading.style.cursor = 'pointer';
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layout', ['title' => $title], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/wither-birth/projects/laravel_report_system/resources/views/report.blade.php ENDPATH**/ ?>