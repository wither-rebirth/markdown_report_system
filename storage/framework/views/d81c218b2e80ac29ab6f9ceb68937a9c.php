<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title><?php echo e($title ?? 'Laravel 靶场报告系统'); ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="description" content="个人靶场报告展示系统">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- 预加载关键资源 -->
    <link rel="preload" href="<?php echo e(asset('css/app.css')); ?>" as="style">
    <link rel="preload" href="<?php echo e(asset('js/app.js')); ?>" as="script">
    
    <!-- 引入样式文件 -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <div id="app">
        <!-- 导航栏 -->
        <header class="navbar">
            <div class="container">
                <h1><a href="<?php echo e(url('/')); ?>">🎯 靶场报告系统</a></h1>
                <nav class="navbar-nav">
                    <a href="<?php echo e(url('/')); ?>" class="nav-link">首页</a>
                    <button onclick="toggleFullscreen()" class="nav-btn" title="全屏切换">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
                        </svg>
                    </button>
                </nav>
            </div>
        </header>

        <!-- 主要内容 -->
        <main>
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <!-- 页脚 -->
        <footer>
            <div class="container">
                <p>
                    © <?php echo e(date('Y')); ?> Laravel 靶场报告系统 | 
                    <a href="https://github.com" target="_blank" rel="noopener">GitHub</a> |
                    <a href="#" onclick="toggleDarkMode()">切换主题</a>
                </p>
            </div>
        </footer>

        <!-- 回到顶部按钮 -->
        <button 
            id="scroll-top-btn" 
            class="scroll-top"
            title="回到顶部"
            style="display: none;"
            onclick="scrollToTop()"
        >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/>
            </svg>
        </button>

        <!-- 加载指示器 -->
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading"></div>
        </div>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    
    <script>
        // 全屏切换函数
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        // 暗黑模式切换
        function toggleDarkMode() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('dark-mode', isDark);
        }

        // 回到顶部功能
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // 初始化暗黑模式
        if (localStorage.getItem('dark-mode') === 'true') {
            document.documentElement.classList.add('dark');
        }

        // 监听系统主题变化
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('dark-mode')) {
                document.documentElement.classList.toggle('dark', e.matches);
            }
        });

        // 滚动时显示/隐藏回到顶部按钮
        window.addEventListener('scroll', () => {
            const scrollTopBtn = document.getElementById('scroll-top-btn');
            if (window.scrollY > 100) {
                scrollTopBtn.style.display = 'flex';
            } else {
                scrollTopBtn.style.display = 'none';
            }
        });

        // 键盘快捷键
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K 聚焦搜索框
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('report-search');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // F11 全屏
            if (e.key === 'F11') {
                e.preventDefault();
                toggleFullscreen();
            }
            
            // Escape 退出全屏
            if (e.key === 'Escape' && document.fullscreenElement) {
                document.exitFullscreen();
            }
        });

        // 性能监控
        window.addEventListener('load', () => {
            if ('performance' in window) {
                setTimeout(() => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    console.log('页面加载时间:', perfData.loadEventEnd - perfData.navigationStart, 'ms');
                }, 0);
            }
        });
    </script>
</body>
</html>

<?php /**PATH /Users/wither-birth/projects/laravel_report_system/resources/views/layout.blade.php ENDPATH**/ ?>