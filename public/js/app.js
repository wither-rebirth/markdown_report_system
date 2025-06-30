// Laravel Report System JavaScript

// 文档加载完成后执行
document.addEventListener('DOMContentLoaded', function() {
    
    // 初始化报告搜索功能
    initReportSearch();
    
    // 初始化代码高亮
    initCodeHighlight();
    
    // 初始化目录生成
    initTableOfContents();
    
    console.log('Laravel Report System initialized');
});

/**
 * 初始化报告搜索功能
 */
function initReportSearch() {
    const searchInput = document.getElementById('report-search');
    const reportCards = document.querySelectorAll('.report-card');
    
    if (searchInput && reportCards.length > 0) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            
            reportCards.forEach(card => {
                const title = card.querySelector('.report-title').textContent.toLowerCase();
                const meta = card.querySelector('.report-meta').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || meta.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
}

/**
 * 初始化代码高亮
 */
function initCodeHighlight() {
    // 为代码块添加复制按钮
    const codeBlocks = document.querySelectorAll('pre code');
    
    codeBlocks.forEach(block => {
        const pre = block.parentElement;
        const button = document.createElement('button');
        button.textContent = '复制';
        button.className = 'copy-btn';
        button.style.cssText = `
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: #374151;
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            cursor: pointer;
        `;
        
        pre.style.position = 'relative';
        pre.appendChild(button);
        
        button.addEventListener('click', () => {
            navigator.clipboard.writeText(block.textContent).then(() => {
                button.textContent = '已复制!';
                setTimeout(() => {
                    button.textContent = '复制';
                }, 2000);
            });
        });
    });
}

/**
 * 初始化目录生成
 */
function initTableOfContents() {
    const content = document.querySelector('.report-content');
    const tocContainer = document.getElementById('table-of-contents');
    
    if (content && tocContainer) {
        const headings = content.querySelectorAll('h1, h2, h3, h4, h5, h6');
        
        if (headings.length > 0) {
            const toc = document.createElement('ul');
            toc.className = 'toc-list';
            
            headings.forEach((heading, index) => {
                // 为标题添加ID
                if (!heading.id) {
                    heading.id = `heading-${index}`;
                }
                
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = `#${heading.id}`;
                a.textContent = heading.textContent;
                a.className = `toc-${heading.tagName.toLowerCase()}`;
                
                li.appendChild(a);
                toc.appendChild(li);
            });
            
            tocContainer.appendChild(toc);
        }
    }
}

/**
 * 平滑滚动到目标元素
 */
function smoothScrollTo(targetId) {
    const target = document.getElementById(targetId);
    if (target) {
        target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

/**
 * 格式化日期显示
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * 全屏模式切换
 */
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
} 