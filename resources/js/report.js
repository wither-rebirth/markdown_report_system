// Report 页面 JavaScript

// 生成目录
function generateTableOfContents() {
    const headings = document.querySelectorAll('.report-content h1, .report-content h2, .report-content h3, .report-content h4, .report-content h5, .report-content h6');
    
    // 如果标题数量少于3个，隐藏侧边栏
    if (headings.length < 3) {
        const sidebar = document.querySelector('.report-sidebar');
        if (sidebar) {
            sidebar.style.display = 'none';
            // 调整主内容区域样式
            const mainContent = document.querySelector('.report-main');
            if (mainContent) {
                mainContent.style.marginLeft = '0';
            }
        }
        return;
    }
    
    const tocContainer = document.getElementById('table-of-contents');
    if (!tocContainer) return;
    
    // 检查报告内容中是否已经包含目录
    const reportContent = document.querySelector('.report-content');
    if (reportContent) {
        const existingToc = reportContent.querySelector('ul, ol');
        const tocKeywords = ['目录', '目次', 'table of contents', 'toc', 'contents'];
        
        if (existingToc) {
            const tocText = existingToc.textContent.toLowerCase();
            const parentText = existingToc.parentElement ? existingToc.parentElement.textContent.toLowerCase() : '';
            
            // 如果找到了可能的目录，检查是否包含目录关键词
            const hasKeywords = tocKeywords.some(keyword => 
                tocText.includes(keyword) || parentText.includes(keyword)
            );
            
            if (hasKeywords) {
                console.log('检测到现有目录，跳过自动生成');
                const sidebar = document.querySelector('.report-sidebar');
                if (sidebar) {
                    sidebar.style.display = 'none';
                    const mainContent = document.querySelector('.report-main');
                    if (mainContent) {
                        mainContent.style.marginLeft = '0';
                    }
                }
                return;
            }
        }
    }
    
    // 创建目录列表
    const tocList = document.createElement('ul');
    tocList.className = 'toc-list';
    
    headings.forEach((heading, index) => {
        // 为标题添加 ID
        if (!heading.id) {
            heading.id = 'heading-' + index;
        }
        
        // 创建目录项
        const tocItem = document.createElement('li');
        const tocLink = document.createElement('a');
        
        tocLink.href = '#' + heading.id;
        tocLink.textContent = heading.textContent;
        tocLink.className = 'toc-' + heading.tagName.toLowerCase();
        tocLink.dataset.target = heading.id;
        
        // 添加点击事件
        tocLink.addEventListener('click', function(e) {
            e.preventDefault();
            scrollToHeading(heading.id);
        });
        
        tocItem.appendChild(tocLink);
        tocList.appendChild(tocItem);
    });
    
    tocContainer.appendChild(tocList);
    
    // 初始化滚动监听
    initScrollSpy();
}

// 滚动到指定标题
function scrollToHeading(headingId) {
    const heading = document.getElementById(headingId);
    if (heading) {
        // 计算偏移量，考虑固定头部
        const headerOffset = 80;
        const elementPosition = heading.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
        
        // 更新 URL 哈希
        window.history.pushState(null, null, '#' + headingId);
        
        // 添加高亮效果
        heading.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
        heading.style.transition = 'background-color 0.3s ease';
        
        setTimeout(() => {
            heading.style.backgroundColor = '';
        }, 2000);
    }
}

// 滚动监听 - 高亮当前章节
function initScrollSpy() {
    const headings = document.querySelectorAll('.report-content h1, .report-content h2, .report-content h3, .report-content h4, .report-content h5, .report-content h6');
    const tocLinks = document.querySelectorAll('.toc-list a');
    
    if (headings.length === 0 || tocLinks.length === 0) return;
    
    function updateActiveTocLink() {
        let currentActiveHeading = null;
        const scrollPosition = window.scrollY + 100; // 偏移量

        // 找到当前显示的标题
        for (let i = headings.length - 1; i >= 0; i--) {
            const heading = headings[i];
            if (heading.offsetTop <= scrollPosition) {
                currentActiveHeading = heading;
                break;
            }
        }

        // 更新目录链接状态
        tocLinks.forEach(link => {
            link.classList.remove('active');
            if (currentActiveHeading && link.dataset.target === currentActiveHeading.id) {
                link.classList.add('active');
                
                // 滚动目录到可见区域
                const tocContainer = document.querySelector('.toc-container');
                if (tocContainer) {
                    const linkRect = link.getBoundingClientRect();
                    const containerRect = tocContainer.getBoundingClientRect();
                    
                    if (linkRect.top < containerRect.top || linkRect.bottom > containerRect.bottom) {
                        link.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            }
        });
    }

    // 节流滚动事件
    let ticking = false;
    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(() => {
                updateActiveTocLink();
                ticking = false;
            });
            ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll);
    updateActiveTocLink(); // 初始调用
}

// 侧边栏切换功能
function toggleTocSidebar() {
    const sidebar = document.querySelector('.report-sidebar');
    const mainContent = document.querySelector('.report-main');
    
    if (sidebar && mainContent) {
        // 检查是否为移动端
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile) {
            // 移动端：切换可见性
            sidebar.classList.toggle('mobile-visible');
            
            // 添加遮罩层
            if (sidebar.classList.contains('mobile-visible')) {
                createMobileOverlay();
            } else {
                removeMobileOverlay();
            }
        } else {
            // 桌面端：切换收起状态
            sidebar.classList.toggle('collapsed');
            
            // 保存状态到 localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('toc-sidebar-collapsed', isCollapsed);
        }
    }
}

// 创建移动端遮罩层
function createMobileOverlay() {
    const existingOverlay = document.querySelector('.mobile-overlay');
    if (existingOverlay) return;
    
    const overlay = document.createElement('div');
    overlay.className = 'mobile-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    document.body.appendChild(overlay);
    
    // 渐入效果
    setTimeout(() => {
        overlay.style.opacity = '1';
    }, 10);
    
    // 点击遮罩层关闭侧边栏
    overlay.addEventListener('click', () => {
        const sidebar = document.querySelector('.report-sidebar');
        if (sidebar) {
            sidebar.classList.remove('mobile-visible');
            removeMobileOverlay();
        }
    });
}

// 移除移动端遮罩层
function removeMobileOverlay() {
    const overlay = document.querySelector('.mobile-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    }
}

// 处理窗口大小变化
function handleResize() {
    const sidebar = document.querySelector('.report-sidebar');
    if (!sidebar) return;
    
    const isMobile = window.innerWidth <= 768;
    
    if (!isMobile) {
        // 桌面端：移除移动端相关类和遮罩
        sidebar.classList.remove('mobile-visible');
        removeMobileOverlay();
        
        // 恢复桌面端状态
        const isCollapsed = localStorage.getItem('toc-sidebar-collapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
        }
    } else {
        // 移动端：移除桌面端状态
        sidebar.classList.remove('collapsed');
    }
}

// 恢复侧边栏状态
function restoreSidebarState() {
    const isCollapsed = localStorage.getItem('toc-sidebar-collapsed') === 'true';
    const sidebar = document.querySelector('.report-sidebar');
    
    if (isCollapsed && sidebar) {
        sidebar.classList.add('collapsed');
    }
}

// 为标题添加锚点链接功能
function initHeadingAnchors() {
    const headings = document.querySelectorAll('.report-content h1, .report-content h2, .report-content h3, .report-content h4, .report-content h5, .report-content h6');
    
    headings.forEach((heading, index) => {
        if (!heading.id) {
            heading.id = 'heading-' + index;
        }
        
        // 添加点击事件
        heading.addEventListener('click', function() {
            const url = window.location.href.split('#')[0] + '#' + heading.id;
            
            // 复制链接到剪贴板
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(() => {
                    showToast('链接已复制到剪贴板');
                }).catch(() => {
                    console.log('无法复制链接');
                });
            } else {
                // 降级处理
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showToast('链接已复制到剪贴板');
                } catch (err) {
                    console.log('无法复制链接');
                }
                if (textArea.parentNode) {
                    textArea.parentNode.removeChild(textArea);
                }
            }
            
            // 更新 URL
            window.history.pushState(null, null, '#' + heading.id);
        });
        
        // 添加鼠标悬停样式
        heading.style.cursor = 'pointer';
        heading.title = '点击复制链接';
    });
}

// 显示提示消息
function showToast(message) {
    // 创建提示元素
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--primary-color);
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        font-size: 14px;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    // 显示动画
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // 自动隐藏
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}



// 代码块复制功能和样式增强
function initCodeCopy() {
    const codeBlocks = document.querySelectorAll('.report-content pre');
    
    codeBlocks.forEach(block => {
        // 智能检测代码块类型
        const codeElement = block.querySelector('code');
        if (codeElement) {
            const content = codeElement.textContent.toLowerCase();
            
            // 检测不同类型的命令行
            if (content.includes('sudo') || content.includes('root@') || content.includes('#')) {
                block.classList.add('terminal-root');
            } else if (content.includes('c:\\') || content.includes('cmd') || content.includes('powershell')) {
                block.classList.add('terminal-windows');
            } else if (content.includes('>>>') || content.includes('python') || content.includes('pip')) {
                block.classList.add('terminal-python');
            }
        }
        
        // 创建 Kali Linux 标题栏
        const titleBar = document.createElement('div');
        titleBar.style.cssText = `
            position: absolute;
            top: 12px;
            right: 60px;
            color: #00ff41;
            font-size: 10px;
            font-family: 'Ubuntu Mono', 'Consolas', 'Monaco', 'Courier New', monospace;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
            transition: opacity 0.3s ease;
            text-shadow: 0 0 5px rgba(0, 255, 65, 0.5);
        `;
        
        if (block.classList.contains('terminal-root')) {
            titleBar.textContent = 'ROOT@KALI';
        } else if (block.classList.contains('terminal-windows')) {
            titleBar.textContent = 'CMD.EXE';
        } else if (block.classList.contains('terminal-python')) {
            titleBar.textContent = 'PYTHON3';
        } else {
            titleBar.textContent = 'KALI@LINUX';
        }
        
        block.appendChild(titleBar);
        
        // 创建 Kali Linux 风格复制按钮
        const copyButton = document.createElement('button');
        copyButton.innerHTML = '⚡';
        copyButton.title = '复制命令';
        copyButton.style.cssText = `
            position: absolute;
            top: 5px;
            right: 16px;
            background: transparent;
            color: #00ff41;
            border: 1px solid #00ff41;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s ease;
            font-family: inherit;
            text-shadow: 0 0 5px rgba(0, 255, 65, 0.5);
            box-shadow: 0 0 5px rgba(0, 255, 65, 0.2);
        `;
        
        // 设置代码块为相对定位
        block.style.position = 'relative';
        
        // 添加复制按钮
        block.appendChild(copyButton);
        
        // Kali Linux 风格悬停效果
        block.addEventListener('mouseenter', () => {
            copyButton.style.opacity = '1';
            copyButton.style.background = 'rgba(0, 255, 65, 0.1)';
            copyButton.style.borderColor = '#39ff14';
            titleBar.style.opacity = '1';
        });
        
        block.addEventListener('mouseleave', () => {
            copyButton.style.opacity = '0';
            copyButton.style.background = 'transparent';
            copyButton.style.borderColor = '#00ff41';
            titleBar.style.opacity = '0.8';
        });
        
        // Kali Linux 复制按钮悬停效果
        copyButton.addEventListener('mouseenter', () => {
            copyButton.style.background = 'rgba(57, 255, 20, 0.2)';
            copyButton.style.color = '#39ff14';
            copyButton.style.transform = 'scale(1.1)';
            copyButton.style.boxShadow = '0 0 15px rgba(57, 255, 20, 0.5)';
        });
        
        copyButton.addEventListener('mouseleave', () => {
            copyButton.style.background = 'rgba(0, 255, 65, 0.1)';
            copyButton.style.color = '#00ff41';
            copyButton.style.transform = 'scale(1)';
            copyButton.style.boxShadow = '0 0 5px rgba(0, 255, 65, 0.2)';
        });
        
        // 复制功能
        copyButton.addEventListener('click', () => {
            const code = block.querySelector('code') || block;
            let text = code.textContent;
            
            // 移除提示符，只复制实际命令
            if (block.classList.contains('terminal-root')) {
                text = text.replace(/^# /gm, '');
            } else if (block.classList.contains('terminal-windows')) {
                text = text.replace(/^C:\\> /gm, '');
            } else if (block.classList.contains('terminal-python')) {
                text = text.replace(/^>>> /gm, '');
            } else {
                text = text.replace(/^\$ /gm, '');
            }
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    copyButton.innerHTML = '💀';
                    copyButton.style.color = '#39ff14';
                    copyButton.style.textShadow = '0 0 10px rgba(57, 255, 20, 0.8)';
                    copyButton.style.boxShadow = '0 0 20px rgba(57, 255, 20, 0.6)';
                    setTimeout(() => {
                        copyButton.innerHTML = '⚡';
                        copyButton.style.color = '#00ff41';
                        copyButton.style.textShadow = '0 0 5px rgba(0, 255, 65, 0.5)';
                        copyButton.style.boxShadow = '0 0 5px rgba(0, 255, 65, 0.2)';
                    }, 2000);
                });
            } else {
                // 降级处理
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    copyButton.innerHTML = '💀';
                    copyButton.style.color = '#39ff14';
                    copyButton.style.textShadow = '0 0 10px rgba(57, 255, 20, 0.8)';
                    copyButton.style.boxShadow = '0 0 20px rgba(57, 255, 20, 0.6)';
                    setTimeout(() => {
                        copyButton.innerHTML = '⚡';
                        copyButton.style.color = '#00ff41';
                        copyButton.style.textShadow = '0 0 5px rgba(0, 255, 65, 0.5)';
                        copyButton.style.boxShadow = '0 0 5px rgba(0, 255, 65, 0.2)';
                    }, 2000);
                } catch (err) {
                    console.log('无法复制代码');
                }
                if (textArea.parentNode) {
                    textArea.parentNode.removeChild(textArea);
                }
            }
        });
    });
}

// 图片懒加载
function initImageLazyLoad() {
    const images = document.querySelectorAll('.report-content img');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        images.forEach(img => {
            if (img.dataset.src) {
                imageObserver.observe(img);
            }
        });
    }
}

// 键盘快捷键
function initKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        // P 键打印
        if (e.key === 'p' && e.ctrlKey) {
            e.preventDefault();
            window.print();
        }
        
        // Home 键回到顶部
        if (e.key === 'Home') {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // End 键到底部
        if (e.key === 'End') {
            e.preventDefault();
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        }
    });
}

// 阅读进度条
function initReadingProgress() {
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: var(--primary-color);
        z-index: 9999;
        transition: width 0.1s ease;
    `;
    
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', () => {
        const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollTop = window.pageYOffset;
        const progress = (scrollTop / scrollHeight) * 100;
        
        progressBar.style.width = progress + '%';
    });
}

// 全屏切换
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
}

// 页面初始化
document.addEventListener('DOMContentLoaded', function() {
    // 恢复侧边栏状态
    restoreSidebarState();
    
    // 生成目录
    generateTableOfContents();
    
    // 初始化标题锚点
    initHeadingAnchors();
    
    // 监听窗口大小变化
    window.addEventListener('resize', handleResize);
    

    
    // 初始化代码复制
    initCodeCopy();
    
    // 初始化图片懒加载
    initImageLazyLoad();
    
    // 初始化键盘快捷键
    initKeyboardShortcuts();
    
    // 初始化阅读进度条
    initReadingProgress();
    
    // 处理 URL 哈希
    if (window.location.hash) {
        const headingId = window.location.hash.substring(1);
        setTimeout(() => {
            scrollToHeading(headingId);
        }, 100);
    }
    
    console.log('Report 页面初始化完成');
});

// 导出函数供其他脚本使用
window.ReportPage = {
    generateTableOfContents,
    scrollToHeading,
    initHeadingAnchors,
    initScrollSpy,
    toggleTocSidebar,
    showToast,
    toggleFullscreen
};

// 全局函数（供HTML调用）
window.toggleTocSidebar = toggleTocSidebar; 