// Layout JavaScript - 全局布局相关脚本

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
function initDarkMode() {
    if (localStorage.getItem('dark-mode') === 'true') {
        document.documentElement.classList.add('dark');
    }
}

// 监听系统主题变化
function initSystemThemeWatcher() {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('dark-mode')) {
            document.documentElement.classList.toggle('dark', e.matches);
        }
    });
}

// 滚动时显示/隐藏回到顶部按钮
function initScrollTopButton() {
    window.addEventListener('scroll', () => {
        const scrollTopBtn = document.getElementById('scroll-top-btn');
        if (scrollTopBtn) {
            if (window.scrollY > 100) {
                scrollTopBtn.style.display = 'flex';
            } else {
                scrollTopBtn.style.display = 'none';
            }
        }
    });
}

// 键盘快捷键
function initKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
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
}

// 性能监控
function initPerformanceMonitoring() {
    window.addEventListener('load', () => {
        if ('performance' in window) {
            setTimeout(() => {
                const perfData = performance.getEntriesByType('navigation')[0];
                console.log('页面加载时间:', perfData.loadEventEnd - perfData.navigationStart, 'ms');
            }, 0);
        }
    });
}

// 加载指示器控制
function showLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
    }
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
}

// 页面初始化
document.addEventListener('DOMContentLoaded', function() {
    // 初始化各种功能
    initDarkMode();
    initSystemThemeWatcher();
    initScrollTopButton();
    initKeyboardShortcuts();
    initPerformanceMonitoring();
    
    // 页面加载完成后隐藏加载指示器
    hideLoading();
});

// 页面卸载前显示加载指示器
window.addEventListener('beforeunload', function() {
    showLoading();
}); 