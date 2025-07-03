// Index 页面 JavaScript

// 简化的搜索功能
function initSearch() {
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
                    const resultsCount = document.getElementById('results-count');
                    if (resultsCount) {
                        resultsCount.textContent = visibleCount;
                    }
                }
            }
        });
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
    });
}

// 页面加载动画
function initLoadAnimation() {
    const cards = document.querySelectorAll('.report-card');
    cards.forEach((card, index) => {
        // 移除内联样式，使用 CSS 动画
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
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
            console.log('页面隐藏，暂停动画');
        } else {
            // 页面可见时恢复操作
            console.log('页面可见，恢复动画');
        }
    });
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
    
    // 页面加载完成提示
    console.log('Index 页面初始化完成');
});

// 导出函数供其他脚本使用
window.IndexPage = {
    initSearch,
    initKeyboardShortcuts,
    initLoadAnimation,
    initCardHoverEffects
}; 