<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title><?php echo e($title ?? 'Laravel 靶场报告系统'); ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="个人靶场报告展示系统">
    
    <!-- 引入样式文件 -->
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <!-- 导航栏 -->
    <header class="navbar">
        <div class="container">
            <h1><a href="<?php echo e(url('/')); ?>" style="color: white; text-decoration: none;">🎯 靶场报告系统</a></h1>
        </div>
    </header>

    <!-- 主要内容 -->
    <main class="container">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- 页脚 -->
    <footer class="container">
        <hr>
        <p style="text-align: center; color: var(--secondary-color); font-size: 0.875rem;">
            © <?php echo e(date('Y')); ?> Laravel 靶场报告系统 | 
            <a href="https://github.com" target="_blank">GitHub</a>
        </p>
    </footer>

    <!-- 引入JavaScript文件 -->
    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>

<?php /**PATH /Users/wither-birth/projects/laravel_report_system/resources/views/layout.blade.php ENDPATH**/ ?>