// Layout JavaScript - 全局布局相关脚本

// 全屏切换函数
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
}

// 增强的暗黑模式切换系统
function toggleDarkMode() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    
    if (currentTheme === 'dark') {
        html.removeAttribute('data-theme');
        localStorage.setItem('theme-mode', 'light');
        updateThemeIcon('light');
    } else {
        html.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme-mode', 'dark');
        updateThemeIcon('dark');
    }
    
    // 添加切换动画效果
    html.style.transition = 'all 0.3s ease';
    setTimeout(() => {
        html.style.transition = '';
    }, 300);
}

// 将函数暴露给全局作用域
window.toggleFullscreen = toggleFullscreen;
window.toggleDarkMode = toggleDarkMode;

// 更新主题图标
function updateThemeIcon(theme) {
    const themeBtn = document.querySelector('[onclick="toggleDarkMode()"]');
    if (!themeBtn) return;
    
    const icon = themeBtn.querySelector('svg path');
    if (!icon) return;
    
    if (theme === 'dark') {
        // 太阳图标 (亮色模式)
        icon.setAttribute('d', 'M12,18V19A1,1 0 0,0 13,20H11A1,1 0 0,0 12,19V18M6,6L4.93,4.93C4.54,4.54 3.91,4.54 3.5,4.93C3.09,5.32 3.09,5.95 3.5,6.36L4.93,7.79L6,6M18,6L19.07,4.93C19.46,4.54 20.09,4.54 20.5,4.93C20.91,5.32 20.91,5.95 20.5,6.36L19.07,7.79L18,6M12,2A1,1 0 0,0 11,3V4A1,1 0 0,0 12,5A1,1 0 0,0 13,4V3A1,1 0 0,0 12,2M20,11V13A1,1 0 0,0 21,12A1,1 0 0,0 20,11M4,11V13A1,1 0 0,0 5,12A1,1 0 0,0 4,11M12.5,8A4.5,4.5 0 0,0 8,12.5A4.5,4.5 0 0,0 12.5,17A4.5,4.5 0 0,0 17,12.5A4.5,4.5 0 0,0 12.5,8Z');
        themeBtn.setAttribute('title', '切换到亮色模式');
    } else {
        // 月亮图标 (暗色模式)
        icon.setAttribute('d', 'M17.75,4.09L15.22,6.03L16.13,9.09L13.5,7.28L10.87,9.09L11.78,6.03L9.25,4.09L12.44,4L13.5,1L14.56,4L17.75,4.09M21.25,11L19.61,12.25L20.2,14.23L18.5,13.06L16.8,14.23L17.39,12.25L15.75,11L17.81,10.95L18.5,9L19.19,10.95L21.25,11M18.97,15.95C19.8,15.87 20.69,17.05 20.16,17.8C19.84,18.25 19.5,18.67 19.08,19.07C15.17,23 8.84,23 4.94,19.07C1.03,15.17 1.03,8.83 4.94,4.93C5.34,4.53 5.76,4.17 6.21,3.85C6.96,3.32 8.14,4.21 8.06,5.04C7.79,7.9 8.75,10.87 10.95,13.06C13.14,15.26 16.1,16.22 18.97,15.95M17.33,17.97C14.5,17.81 11.7,16.64 9.53,14.5C7.36,12.31 6.2,9.5 6.04,6.68C3.23,9.82 3.34,14.4 6.35,17.41C9.37,20.43 14,20.54 17.33,17.97Z');
        themeBtn.setAttribute('title', '切换到暗黑模式');
    }
}

// 回到顶部功能
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// 将 scrollToTop 也暴露给全局作用域
window.scrollToTop = scrollToTop;

// 初始化主题系统
function initThemeSystem() {
    const savedTheme = localStorage.getItem('theme-mode');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
        document.documentElement.setAttribute('data-theme', 'dark');
        updateThemeIcon('dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
        updateThemeIcon('light');
    }
}

// 监听系统主题变化
function initSystemThemeWatcher() {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        const savedTheme = localStorage.getItem('theme-mode');
        if (!savedTheme) {
            if (e.matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
                updateThemeIcon('dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
                updateThemeIcon('light');
            }
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
    initThemeSystem();
    initSystemThemeWatcher();
    initScrollTopButton();
    initKeyboardShortcuts();
    initPerformanceMonitoring();
    
    // 页面加载完成后隐藏加载指示器
    hideLoading();
    
    // 添加页面加载动画
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.3s ease';
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
});

// 页面卸载前显示加载指示器
window.addEventListener('beforeunload', function() {
    showLoading();
}); 