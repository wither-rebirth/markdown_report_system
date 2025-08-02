// Index 页面 JavaScript

// 清除搜索功能
function clearSearch() {
    const searchInput = document.getElementById('report-search');
    if (searchInput) {
        searchInput.value = '';
        // 触发表单提交来清除搜索
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.submit();
        }
    }
}

// 改进的搜索功能
function initSearch() {
    const searchInput = document.getElementById('report-search');
    const searchForm = document.querySelector('.search-form');
    
    if (searchInput && searchForm) {
        // 实时搜索提示（可选）
        let searchTimeout;
        
        // 搜索输入事件
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            // 防抖处理，避免频繁搜索
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.trim();
                if (searchTerm.length > 0) {
                    // 可以在这里添加实时搜索建议功能
                    console.log('搜索词:', searchTerm);
                }
            }, 300);
        });
        
        // 回车键搜索
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.submit();
            }
        });
        
        // 自动聚焦搜索框（如果有搜索内容）
        if (searchInput.value.trim() !== '') {
            searchInput.focus();
            // 将光标移到末尾
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
    }
}

// 快捷键支持
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+K 聚焦搜索框
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('report-search');
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // 分页快捷键支持
        // 仅在没有焦点在输入框时才响应
        if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            const url = new URL(window.location.href);
            const currentPage = parseInt(url.searchParams.get('page') || 1);
            
            // 左箭头或 A 键 - 上一页
            if ((e.key === 'ArrowLeft' || e.key.toLowerCase() === 'a') && currentPage > 1) {
                e.preventDefault();
                url.searchParams.set('page', currentPage - 1);
                window.location.href = url.toString();
            }
            
            // 右箭头或 D 键 - 下一页
            if (e.key === 'ArrowRight' || e.key.toLowerCase() === 'd') {
                e.preventDefault();
                url.searchParams.set('page', currentPage + 1);
                window.location.href = url.toString();
            }
            
            // Home 键 - 第一页
            if (e.key === 'Home') {
                e.preventDefault();
                url.searchParams.set('page', 1);
                window.location.href = url.toString();
            }
            
            // End 键 - 最后一页
            if (e.key === 'End') {
                const jumpInput = document.getElementById('jumpToPage');
                if (jumpInput) {
                    e.preventDefault();
                    const maxPage = parseInt(jumpInput.getAttribute('max'));
                    url.searchParams.set('page', maxPage);
                    window.location.href = url.toString();
                }
            }
        }
    });
}

// 页面加载动画 - 性能优化版本
function initLoadAnimation() {
    const cards = document.querySelectorAll('.report-card, .category-card');
    
    if (cards.length === 0) return;
    
    // 使用 requestAnimationFrame 优化性能
    requestAnimationFrame(() => {
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.willChange = 'opacity, transform';
            
            // 限制最大延迟，避免过长的动画
            const delay = Math.min(index * 50, 300);
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
                
                // 动画完成后移除 will-change
                setTimeout(() => {
                    card.style.willChange = 'auto';
                }, 400);
            }, delay);
        });
    });
}

// 卡片悬停效果增强
function initCardHoverEffects() {
    const cards = document.querySelectorAll('.report-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            // 添加悬停时的微动画
            this.style.transform = 'translateY(-2px) scale(1.01)';
        });
        
        card.addEventListener('mouseleave', function() {
            // 恢复原始状态
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// 懒加载图片（如果有的话）
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // 降级处理
        images.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }
}

// 页面可见性 API - 优化性能
function initVisibilityAPI() {
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // 页面不可见时暂停某些操作
            document.documentElement.style.animationPlayState = 'paused';
        } else {
            // 页面可见时恢复操作
            document.documentElement.style.animationPlayState = 'running';
        }
    });
}

// 滚动性能优化
function initScrollOptimization() {
    let ticking = false;
    
    // 节流滚动事件
    function updateOnScroll() {
        // 检查元素可见性，优化动画
        const cards = document.querySelectorAll('.category-card, .report-card');
        const viewportHeight = window.innerHeight;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        cards.forEach(card => {
            const rect = card.getBoundingClientRect();
            const isVisible = rect.top < viewportHeight && rect.bottom > 0;
            
            if (isVisible) {
                card.style.willChange = 'transform';
            } else {
                card.style.willChange = 'auto';
            }
        });
        
        ticking = false;
    }
    
    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(updateOnScroll);
            ticking = true;
        }
    }
    
    // 使用被动监听器提升性能
    window.addEventListener('scroll', onScroll, { passive: true });
}

// 搜索历史记录
function initSearchHistory() {
    const searchInput = document.getElementById('report-search');
    if (!searchInput) return;
    
    const SEARCH_HISTORY_KEY = 'report_search_history';
    const MAX_HISTORY = 5;
    
    // 获取搜索历史
    function getSearchHistory() {
        try {
            return JSON.parse(localStorage.getItem(SEARCH_HISTORY_KEY) || '[]');
        } catch {
            return [];
        }
    }
    
    // 保存搜索历史
    function saveSearchHistory(query) {
        if (!query.trim()) return;
        
        const history = getSearchHistory();
        const updatedHistory = [query, ...history.filter(item => item !== query)].slice(0, MAX_HISTORY);
        
        localStorage.setItem(SEARCH_HISTORY_KEY, JSON.stringify(updatedHistory));
    }
    
    // 监听搜索输入
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            saveSearchHistory(this.value);
        }
    });
}

// 分页功能已简化为固定每页10个

function jumpToPage() {
    const pageInput = document.getElementById('jumpToPage');
    const page = parseInt(pageInput.value);
    const maxPage = parseInt(pageInput.getAttribute('max'));
    
    if (page >= 1 && page <= maxPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
    } else {
        alert(`请输入 1 到 ${maxPage} 之间的页码`);
        pageInput.focus();
    }
}

// 初始化分页功能
function initPagination() {
    // 快速跳转 - 支持回车键
    const pageInput = document.getElementById('jumpToPage');
    if (pageInput) {
        pageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                jumpToPage();
            }
        });
    }
}

// 页面初始化
document.addEventListener('DOMContentLoaded', function() {
    // 初始化各种功能
    initSearch();
    initKeyboardShortcuts();
    initLoadAnimation();
    initCardHoverEffects();
    initLazyLoading();
    initVisibilityAPI();
    initSearchHistory();
    initPagination(); // 新增分页初始化
    initScrollOptimization(); // 滚动性能优化
    
    // 页面加载完成提示
    console.log('Index 页面初始化完成 - 已启用滚动优化');
});

// 导出函数供其他脚本使用（全局函数）
window.jumpToPage = jumpToPage;
window.clearSearch = clearSearch; // 导出清除搜索函数

// 导出函数供其他脚本使用
window.IndexPage = {
    initSearch,
    initKeyboardShortcuts,
    initLoadAnimation,
    initCardHoverEffects,
    initPagination,
    jumpToPage,
    clearSearch
}; 