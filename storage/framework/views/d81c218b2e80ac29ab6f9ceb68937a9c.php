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
        <!-- 增强的导航栏 -->
        <header class="navbar">
            <div class="navbar-container">
                <!-- 左侧：系统标题和统计 -->
                <div class="navbar-left">
                    <h1 class="navbar-title">
                        <a href="<?php echo e(url('/')); ?>">🎯 靶场报告系统</a>
                    </h1>
                    <?php if(isset($reports) && Route::currentRouteName() === 'reports.index'): ?>
                    <span class="navbar-stats"><?php echo e(count($reports)); ?> 个报告</span>
                    <?php endif; ?>
                </div>
                
                <!-- 中间：搜索框（仅在报告列表页显示） -->
                <?php if(isset($reports) && count($reports) > 0 && Route::currentRouteName() === 'reports.index'): ?>
                <div class="navbar-center">
                    <div class="navbar-search">
                        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                        <input 
                            type="text" 
                            id="report-search" 
                            placeholder="搜索报告..."
                            class="navbar-search-input"
                        >
                        <div class="navbar-search-shortcut">
                            <kbd>⌘K</kbd>
                        </div>
                    </div>
                    <div id="search-results" class="navbar-search-results" style="display: none;">
                        找到 <span class="highlight" id="results-count">0</span> 个结果
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 右侧：导航链接和操作按钮 -->
                <nav class="navbar-right">
                    <a href="<?php echo e(url('/')); ?>" class="nav-link">首页</a>
                    <?php if(Route::currentRouteName() === 'reports.index'): ?>
                    <a href="<?php echo e(route('reports.create')); ?>" class="navbar-upload-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        上传
                    </a>
                    <?php endif; ?>
                    <button onclick="toggleFullscreen()" class="nav-btn" title="全屏切换">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
                        </svg>
                    </button>
                    <button onclick="toggleDarkMode()" class="nav-btn" title="切换主题">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.75,4.09L15.22,6.03L16.13,9.09L13.5,7.28L10.87,9.09L11.78,6.03L9.25,4.09L12.44,4L13.5,1L14.56,4L17.75,4.09M21.25,11L19.61,12.25L20.2,14.23L18.5,13.06L16.8,14.23L17.39,12.25L15.75,11L17.81,10.95L18.5,9L19.19,10.95L21.25,11M18.97,15.95C19.8,15.87 20.69,17.05 20.16,17.8C19.84,18.25 19.5,18.67 19.08,19.07C15.17,23 8.84,23 4.94,19.07C1.03,15.17 1.03,8.83 4.94,4.93C5.34,4.53 5.76,4.17 6.21,3.85C6.96,3.32 8.14,4.21 8.06,5.04C7.79,7.9 8.75,10.87 10.95,13.06C13.14,15.26 16.1,16.22 18.97,15.95M17.33,17.97C14.5,17.81 11.7,16.64 9.53,14.5C7.36,12.31 6.2,9.5 6.04,6.68C3.23,9.82 3.34,14.4 6.35,17.41C9.37,20.43 14,20.54 17.33,17.97Z"/>
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
                    <a href="https://github.com" target="_blank" rel="noopener">GitHub</a>
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