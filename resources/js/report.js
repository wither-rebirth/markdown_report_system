// Report 页面 JavaScript

// 生成目录
function generateTableOfContents() {
    const headings = document.querySelectorAll('.report-content h1, .report-content h2, .report-content h3, .report-content h4, .report-content h5, .report-content h6');
    
    // 如果标题数量少于3个，隐藏侧边栏并居中内容
    if (headings.length < 3) {
        const sidebar = document.querySelector('.report-sidebar');
        if (sidebar) {
            sidebar.style.display = 'none';
            // 调整主内容区域样式为居中布局
            const mainContent = document.querySelector('.report-main');
            if (mainContent) {
                mainContent.classList.add('report-main-centered');
            }
        }
        return;
    }
    
    const tocContainer = document.getElementById('table-of-contents');
    if (!tocContainer) return;
    
        // 检查报告内容中是否已经包含目录 - 改进的检测逻辑
    const reportContent = document.querySelector('.report-content');
    if (reportContent) {
        // 查找可能的目录区域
        const possibleTocElements = reportContent.querySelectorAll('ul, ol');
        // 更严格的目录关键词
        const tocKeywords = ['table of contents', 'toc', '目录', '目次'];
        
        let foundActualToc = false;
        
        for (const element of possibleTocElements) {
            const elementText = element.textContent.toLowerCase();
            const parentText = element.parentElement ? element.parentElement.textContent.toLowerCase() : '';
            const prevSiblingText = element.previousElementSibling ? element.previousElementSibling.textContent.toLowerCase() : '';
            
            // 检查是否有明确的目录关键词
            const hasExplicitTocKeywords = tocKeywords.some(keyword => 
                elementText.includes(keyword) || parentText.includes(keyword) || prevSiblingText.includes(keyword)
            );
            
            // 检查是否包含多个内部链接（指向同一页面的链接）
            const links = element.querySelectorAll('a[href^="#"]');
            const hasMultipleInternalLinks = links.length >= 3;
            
            // 检查是否有明确的章节编号结构
            const hasChapterStructure = /^\s*(\d+\.|\d+\.\d+\.|\w+\.\s|\d+\s)/.test(elementText);
            
            // 排除看起来像命令输出或技术内容的列表
            const looksLikeTechnicalOutput = /\b(tcp|udp|http|https|ssh|port|service|version|nmap|scan|exploit|payload|shell)\b/i.test(elementText);
            
                         // 只有在有明确的目录关键词，且有多个内部链接或章节结构，且不像技术输出时才认为是目录
             if (hasExplicitTocKeywords && (hasMultipleInternalLinks || hasChapterStructure) && !looksLikeTechnicalOutput) {
                 console.log('Found existing table of contents, skipping auto-generation');
                 foundActualToc = true;
                 break;
             }
         }
         
         if (foundActualToc) {
            const sidebar = document.querySelector('.report-sidebar');
            if (sidebar) {
                sidebar.style.display = 'none';
                const mainContent = document.querySelector('.report-main');
                if (mainContent) {
                    mainContent.classList.add('report-main-centered');
                }
            }
            return;
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
            
            // 移动端点击关闭侧边栏
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    const sidebar = document.querySelector('.report-sidebar');
                    const overlay = document.querySelector('.report-sidebar-overlay');
                    if (sidebar && sidebar.classList.contains('mobile-visible')) {
                        sidebar.classList.remove('mobile-visible');
                        if (overlay) {
                            overlay.classList.remove('active');
                        }
                        document.body.style.overflow = '';
                    }
                    removeMobileOverlay();
                }, 300);
            }
        });
        
        tocItem.appendChild(tocLink);
        tocList.appendChild(tocItem);
    });
    
    tocContainer.appendChild(tocList);
    
    // 检查侧边栏是否可见并处理移动端显示
    const sidebar = document.querySelector('.report-sidebar');
    if (sidebar) {
        // 检查是否为移动端，确保侧边栏正确显示
        const isMobile = window.innerWidth <= 768;
        if (isMobile) {
            // 在移动端，侧边栏通过按钮控制显示，但要确保没有被collapsed类隐藏
            sidebar.classList.remove('collapsed');
        } else {
            // 在桌面端，直接显示侧边栏
            sidebar.classList.remove('collapsed');
            sidebar.style.display = 'block';
        }
    }
    
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
                    showToast('Link copied to clipboard');
                }).catch(() => {
                    console.log('Unable to copy link');
                });
            } else {
                // 降级处理
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showToast('Link copied to clipboard');
                } catch (err) {
                    console.log('Unable to copy link');
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
        heading.title = 'Click to copy link';
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
        // 初始化滚动条显示逻辑
        initScrollbarVisibility(block);
        // 智能检测代码块类型并设置data-type属性
        const codeElement = block.querySelector('code');
        if (codeElement) {
            const content = codeElement.textContent;
            const contentLower = content.toLowerCase();
            const firstLine = content.split('\n')[0];
            
            // 检测代码块类型
            if (isCommandBlock(content, contentLower, firstLine)) {
                block.setAttribute('data-type', 'command');
            } else if (isOutputBlock(content, contentLower)) {
                block.setAttribute('data-type', 'output');
            } else if (isCodeBlock(content, contentLower)) {
                block.setAttribute('data-type', 'code');
            }
            // 如果没有明确类型，默认为命令行
            if (!block.hasAttribute('data-type')) {
                block.setAttribute('data-type', 'command');
            }
        }
        
        // 创建简洁的复制按钮
        const copyButton = document.createElement('button');
        copyButton.innerHTML = '📋';
        copyButton.title = 'Copy content';
        copyButton.style.cssText = `
            position: absolute;
            top: 8px;
            right: 12px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 6px 8px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s ease;
            font-family: inherit;
            backdrop-filter: blur(4px);
        `;
        
        // 设置代码块为相对定位
        block.style.position = 'relative';
        
        // 添加复制按钮
        block.appendChild(copyButton);
        
        // 悬停效果
        block.addEventListener('mouseenter', () => {
            copyButton.style.opacity = '1';
        });
        
        block.addEventListener('mouseleave', () => {
            copyButton.style.opacity = '0';
        });
        
        copyButton.addEventListener('mouseenter', () => {
            copyButton.style.background = 'rgba(255, 255, 255, 0.2)';
            copyButton.style.borderColor = 'rgba(255, 255, 255, 0.5)';
            copyButton.style.transform = 'scale(1.05)';
        });
        
        copyButton.addEventListener('mouseleave', () => {
            copyButton.style.background = 'rgba(255, 255, 255, 0.1)';
            copyButton.style.borderColor = 'rgba(255, 255, 255, 0.3)';
            copyButton.style.transform = 'scale(1)';
        });
        
        // 复制功能
        copyButton.addEventListener('click', () => {
            const code = block.querySelector('code') || block;
            let text = code.textContent;
            
            // 如果是命令块，可以选择性地清理一些常见的提示符
            const dataType = block.getAttribute('data-type');
            if (dataType === 'command') {
                // 只移除明显的提示符，保留其他内容
                text = text.replace(/^(\$|#|\w+@\w+[:#]\$?)\s+/gm, '');
            }
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    copyButton.innerHTML = '✅';
                    copyButton.style.color = '#4ade80';
                    setTimeout(() => {
                        copyButton.innerHTML = '📋';
                        copyButton.style.color = '#ffffff';
                    }, 1500);
                    showToast('Content copied to clipboard');
                }).catch(() => {
                    fallbackCopy(text, copyButton);
                });
            } else {
                fallbackCopy(text, copyButton);
            }
        });
    });
}

// 判断是否为命令块
function isCommandBlock(content, contentLower, firstLine) {
    const commandIndicators = [
        'sudo', 'apt', 'yum', 'dnf', 'npm', 'pip', 'git', 'docker', 'kubectl',
        'curl', 'wget', 'ssh', 'scp', 'rsync', 'nmap', 'netstat', 'ps aux',
        'ls -', 'cd ', 'mkdir', 'chmod', 'chown', 'grep', 'find', 'awk', 'sed'
    ];
    
    // 检查是否包含命令行指示符
    if (firstLine.match(/^[\w-]+@[\w-]+[:#]\$?/) ||  // user@host:$ 格式
        firstLine.match(/^[#$]\s/) ||                 // # 或 $ 开头
        firstLine.match(/^C:\\.*?>/) ||               // Windows命令行
        firstLine.match(/^.*@.*[:#]\$.*$/)) {         // 其他命令行格式
        return true;
    }
    
    // 检查是否包含常见命令
    return commandIndicators.some(cmd => contentLower.includes(cmd));
}

// 判断是否为输出块
function isOutputBlock(content, contentLower) {
    const outputIndicators = [
        'total', 'pid', 'uid', 'gid', 'size', 'date', 'time',
        'bytes', 'status', 'response', 'error', 'warning',
        'connected', 'listening', 'running', 'stopped'
    ];
    
    // 如果包含很多数字和空格，可能是输出
    const numbers = (content.match(/\d+/g) || []).length;
    const lines = content.split('\n').length;
    
    if (numbers > lines * 0.3) { // 30%的行包含数字
        return true;
    }
    
    // 检查输出特征
    return outputIndicators.some(indicator => contentLower.includes(indicator));
}

// 判断是否为代码块
function isCodeBlock(content, contentLower) {
    const codeIndicators = [
        'function', 'def ', 'class ', 'import ', 'from ', 'require',
        'const ', 'let ', 'var ', 'if (', 'for (', 'while (', 'try {',
        'public ', 'private ', 'protected ', 'static ', 'void ',
        '#!/bin/', '<?php', '<html', '<script', 'SELECT ', 'INSERT ',
        'UPDATE ', 'DELETE ', 'CREATE TABLE'
    ];
    
    return codeIndicators.some(indicator => contentLower.includes(indicator));
}

// 降级复制方法
function fallbackCopy(text, copyButton) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        copyButton.innerHTML = '✅';
        copyButton.style.color = '#4ade80';
        setTimeout(() => {
            copyButton.innerHTML = '📋';
            copyButton.style.color = '#ffffff';
        }, 1500);
        showToast('Content copied to clipboard');
    } catch (err) {
        console.log('Copy failed:', err);
        showToast('Copy failed, please select text manually');
    }
    
    document.body.removeChild(textArea);
}

// 初始化滚动条显示逻辑
function initScrollbarVisibility(block) {
    const codeElement = block.querySelector('code');
    if (!codeElement) return;
    
    let scrollTimer = null;
    
    // 监听滚动事件
    codeElement.addEventListener('scroll', () => {
        // 添加滚动类，显示滚动条
        block.classList.add('scrolling');
        
        // 清除之前的定时器
        if (scrollTimer) {
            clearTimeout(scrollTimer);
        }
        
        // 停止滚动后延迟隐藏滚动条
        scrollTimer = setTimeout(() => {
            block.classList.remove('scrolling');
        }, 1500); // 1.5秒后隐藏
    });
    
    // 鼠标离开时也清除滚动状态（如果没有在滚动）
    block.addEventListener('mouseleave', () => {
        if (scrollTimer) {
            clearTimeout(scrollTimer);
            // 快速隐藏
            scrollTimer = setTimeout(() => {
                block.classList.remove('scrolling');
            }, 300);
        }
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

// 移动端侧边栏支持
function initMobileSidebar() {
    const sidebar = document.querySelector('.report-sidebar');
    const overlay = document.createElement('div');
    overlay.className = 'report-sidebar-overlay';
    document.body.appendChild(overlay);
    
    // 检测移动端
    const isMobile = () => window.innerWidth <= 768;
    
    // 切换侧边栏显示
    window.toggleTocSidebar = function() {
        if (isMobile()) {
            sidebar.classList.toggle('mobile-visible');
            overlay.classList.toggle('active');
            
            // 防止背景滚动
            if (sidebar.classList.contains('mobile-visible')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        } else {
            sidebar.classList.toggle('collapsed');
        }
    };
    
    // 点击遮罩关闭侧边栏
    overlay.addEventListener('click', () => {
        if (isMobile() && sidebar.classList.contains('mobile-visible')) {
            toggleTocSidebar();
        }
    });
    
    // 响应式处理
    window.addEventListener('resize', () => {
        if (!isMobile()) {
            sidebar.classList.remove('mobile-visible');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // 触摸手势支持
    let startX = 0;
    let startY = 0;
    let currentX = 0;
    let currentY = 0;
    
    document.addEventListener('touchstart', (e) => {
        if (!isMobile()) return;
        
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    });
    
    document.addEventListener('touchmove', (e) => {
        if (!isMobile()) return;
        
        currentX = e.touches[0].clientX;
        currentY = e.touches[0].clientY;
    });
    
    document.addEventListener('touchend', (e) => {
        if (!isMobile()) return;
        
        const deltaX = currentX - startX;
        const deltaY = currentY - startY;
        
        // 水平滑动距离大于垂直滑动距离，且滑动距离超过阈值
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
            if (deltaX > 0 && startX < 50) {
                // 从左边缘向右滑动，打开侧边栏
                if (!sidebar.classList.contains('mobile-visible')) {
                    toggleTocSidebar();
                }
            } else if (deltaX < 0 && sidebar.classList.contains('mobile-visible')) {
                // 向左滑动，关闭侧边栏
                toggleTocSidebar();
            }
        }
    });
}

// 移动端图片优化
function optimizeImagesForMobile() {
    const images = document.querySelectorAll('.report-content img');
    
    images.forEach(img => {
        // 延迟加载
        if (img.dataset.src && !img.src) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                });
            });
            observer.observe(img);
        }
        
        // 点击放大
        img.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                const overlay = document.createElement('div');
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.9);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                    padding: 2rem;
                    cursor: pointer;
                `;
                
                const enlargedImg = img.cloneNode();
                enlargedImg.style.cssText = `
                    max-width: 100%;
                    max-height: 100%;
                    object-fit: contain;
                    border-radius: 8px;
                `;
                
                overlay.appendChild(enlargedImg);
                document.body.appendChild(overlay);
                document.body.style.overflow = 'hidden';
                
                overlay.addEventListener('click', () => {
                    document.body.removeChild(overlay);
                    document.body.style.overflow = '';
                });
            }
        });
    });
}

// 移动端表格优化
function optimizeTablesForMobile() {
    const tables = document.querySelectorAll('.report-content table');
    
    tables.forEach(table => {
        if (window.innerWidth <= 768) {
            // 添加滚动提示
            const wrapper = document.createElement('div');
            wrapper.style.cssText = `
                position: relative;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border: 1px solid var(--border-color);
                border-radius: 8px;
                margin: 1.5rem 0;
            `;
            
            const hint = document.createElement('div');
            hint.style.cssText = `
                position: absolute;
                top: 50%;
                right: 1rem;
                transform: translateY(-50%);
                background: var(--primary-color);
                color: white;
                padding: 0.25rem 0.5rem;
                border-radius: 4px;
                font-size: 0.75rem;
                pointer-events: none;
                opacity: 0.8;
                z-index: 1;
            `;
            hint.textContent = '→ Swipe to view';
            
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
            wrapper.appendChild(hint);
            
            // 滚动时隐藏提示
            wrapper.addEventListener('scroll', () => {
                hint.style.opacity = '0';
            });
        }
    });
}

// 初始化移动端功能
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
    
    // 移动端特有功能
    initMobileSidebar();
    optimizeImagesForMobile();
    optimizeTablesForMobile();
    
    // 移动端性能优化
    if ('serviceWorker' in navigator && window.innerWidth <= 768) {
        // 移动端可以考虑启用 Service Worker 进行缓存
        console.log('Mobile device detected, consider implementing Service Worker for better performance');
    }
    
    console.log('Report page initialization completed');
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