// Home 页面 JavaScript

document.addEventListener('DOMContentLoaded', function() {
    console.log('Home 页面初始化开始...');
    
    // 初始化所有功能
    initTypewriterEffect();
    initTabSwitching();
    initCounterAnimation();
    initScrollReveal();
    initSkillProgressAnimation();
    initParticleSystem();
    initSmoothScrolling();
    initHoverEffects();
    
    console.log('Home 页面初始化完成');
});

// 打字机效果
function initTypewriterEffect() {
    const typewriterElements = document.querySelectorAll('.typewriter');
    
    typewriterElements.forEach(element => {
        const text = element.getAttribute('data-text') || element.textContent;
        element.textContent = '';
        
        let i = 0;
        const typeInterval = setInterval(() => {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
            } else {
                clearInterval(typeInterval);
                // 移除打字光标效果
                setTimeout(() => {
                    element.style.borderRight = 'none';
                }, 1000);
            }
        }, 100);
    });
}

// 标签页切换
function initTabSwitching() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.getAttribute('data-tab');
            
            // 移除所有活动状态
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // 添加活动状态
            button.classList.add('active');
            const targetPane = document.getElementById(targetTab);
            if (targetPane) {
                targetPane.classList.add('active');
                
                // 重新触发动画
                const cards = targetPane.querySelectorAll('.content-card');
                cards.forEach((card, index) => {
                    card.style.animation = 'none';
                    setTimeout(() => {
                        card.style.animation = `fadeInUp 0.6s ease ${index * 0.1}s both`;
                    }, 10);
                });
            }
        });
    });
}

// 数字计数动画
function initCounterAnimation() {
    const counters = document.querySelectorAll('.stat-number[data-count]');
    
    const animateCounter = (counter) => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 2000; // 2秒动画
        const step = target / (duration / 16); // 60fps
        let current = 0;
        
        counter.classList.add('counting');
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
                counter.classList.remove('counting');
            }
            counter.textContent = Math.floor(current).toLocaleString();
        }, 16);
    };
    
    // 使用 Intersection Observer 触发动画
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => observer.observe(counter));
}

// 滚动显示动画
function initScrollReveal() {
    const revealElements = document.querySelectorAll('[data-aos]');
    
    if (!revealElements.length) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const delay = element.getAttribute('data-aos-delay') || 0;
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, delay);
                
                observer.unobserve(element);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    revealElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'all 0.6s ease';
        observer.observe(element);
    });
}

// 技能进度条动画
function initSkillProgressAnimation() {
    const skillBars = document.querySelectorAll('.skill-progress');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBar = entry.target;
                const level = progressBar.getAttribute('data-level');
                const color = progressBar.getAttribute('data-color');
                
                // 设置样式
                progressBar.style.backgroundColor = color;
                progressBar.style.width = '0';
                
                // 动画到目标宽度
                setTimeout(() => {
                    progressBar.style.width = level + '%';
                }, 100);
                
                observer.unobserve(progressBar);
            }
        });
    }, { threshold: 0.5 });
    
    skillBars.forEach(bar => observer.observe(bar));
}

// 粒子系统增强
function initParticleSystem() {
    const particleContainer = document.querySelector('.particle-system');
    if (!particleContainer) return;
    
    // 创建动态粒子
    for (let i = 0; i < 20; i++) {
        createParticle(particleContainer);
    }
}

function createParticle(container) {
    const particle = document.createElement('div');
    particle.className = 'dynamic-particle';
    
    // 随机位置和样式
    const size = Math.random() * 4 + 2;
    const x = Math.random() * 100;
    const y = Math.random() * 100;
    const duration = Math.random() * 20 + 10;
    const delay = Math.random() * 5;
    
    particle.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        background: rgba(59, 130, 246, 0.1);
        border-radius: 50%;
        left: ${x}%;
        top: ${y}%;
        animation: particleFloat ${duration}s infinite linear ${delay}s;
        pointer-events: none;
    `;
    
    container.appendChild(particle);
    
    // 定期重新创建粒子
    setTimeout(() => {
        if (particle.parentNode) {
            particle.remove();
            createParticle(container);
        }
    }, (duration + delay) * 1000);
}

// 平滑滚动
function initSmoothScrolling() {
    const scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', () => {
            const statsSection = document.querySelector('.stats-section');
            if (statsSection) {
                statsSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }
    
    // Hero 区域向下滚动提示
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        let ticking = false;
        
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    const scrollY = window.scrollY;
                    const heroHeight = heroSection.offsetHeight;
                    const opacity = Math.max(0, 1 - (scrollY / heroHeight));
                    
                    heroSection.style.opacity = opacity;
                    ticking = false;
                });
                ticking = true;
            }
        });
    }
}

// 悬停效果增强
function initHoverEffects() {
    // 卡片倾斜效果
    const cards = document.querySelectorAll('.stat-card, .content-card, .tech-category');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', (e) => {
            const rect = card.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            
            card.style.transformOrigin = `${centerX}px ${centerY}px`;
        });
        
        card.addEventListener('mousemove', (e) => {
            if (!card.classList.contains('no-tilt')) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 10;
                const rotateY = (centerX - x) / 10;
                
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
            }
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
        });
    });
    
    // 按钮悬停效果
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// 键盘快捷键
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + 1 切换到博客标签
    if ((e.ctrlKey || e.metaKey) && e.key === '1') {
        e.preventDefault();
        const blogTab = document.querySelector('[data-tab="blog"]');
        if (blogTab) blogTab.click();
    }
    
    // Ctrl/Cmd + 2 切换到报告标签
    if ((e.ctrlKey || e.metaKey) && e.key === '2') {
        e.preventDefault();
        const reportsTab = document.querySelector('[data-tab="reports"]');
        if (reportsTab) reportsTab.click();
    }
    
    // 空格键暂停/恢复动画
    if (e.key === ' ' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
        toggleAnimations();
    }
});

// 切换动画状态
function toggleAnimations() {
    const body = document.body;
    const isAnimationPaused = body.classList.contains('animations-paused');
    
    if (isAnimationPaused) {
        body.classList.remove('animations-paused');
        body.style.setProperty('--animation-play-state', 'running');
    } else {
        body.classList.add('animations-paused');
        body.style.setProperty('--animation-play-state', 'paused');
    }
}

// 性能优化：减少重绘
function optimizeAnimations() {
    // 检测用户偏好
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.body.classList.add('reduced-motion');
        return;
    }
    
    // 检测设备性能
    const isLowPerformance = navigator.hardwareConcurrency <= 2;
    if (isLowPerformance) {
        document.body.classList.add('low-performance');
    }
}

// 懒加载图片
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

// 主题切换动画
function animateThemeSwitch() {
    const body = document.body;
    const overlay = document.createElement('div');
    
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--bg-color);
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    `;
    
    body.appendChild(overlay);
    
    // 淡入
    setTimeout(() => {
        overlay.style.opacity = '1';
    }, 10);
    
    // 淡出
    setTimeout(() => {
        overlay.style.opacity = '0';
        setTimeout(() => {
            body.removeChild(overlay);
        }, 300);
    }, 150);
}

// 错误处理
window.addEventListener('error', (e) => {
    console.error('页面错误:', e.error);
});

// 页面可见性 API
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        // 页面隐藏时暂停动画
        document.body.style.setProperty('--animation-play-state', 'paused');
    } else {
        // 页面可见时恢复动画
        document.body.style.setProperty('--animation-play-state', 'running');
    }
});

// 网络状态检测
window.addEventListener('online', () => {
    console.log('网络连接已恢复');
});

window.addEventListener('offline', () => {
    console.log('网络连接已断开');
});

// 调试工具
if (process.env.NODE_ENV === 'development') {
    window.homeDebug = {
        toggleAnimations,
        animateThemeSwitch,
        optimizeAnimations
    };
}

// 添加 CSS 动画关键帧
const style = document.createElement('style');
style.textContent = `
    @keyframes particleFloat {
        0% {
            transform: translateY(0px) rotate(0deg);
            opacity: 0;
        }
        10% {
            opacity: 1;
        }
        90% {
            opacity: 1;
        }
        100% {
            transform: translateY(-100vh) rotate(360deg);
            opacity: 0;
        }
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animations-paused * {
        animation-play-state: paused !important;
    }
    
    .reduced-motion * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    .low-performance .floating-element {
        display: none;
    }
    
    .low-performance .particle-system {
        display: none;
    }
`;
document.head.appendChild(style);

// 初始化性能优化
optimizeAnimations();
initLazyLoading();

// 页面加载完成后的额外初始化
window.addEventListener('load', () => {
    // 预加载重要资源
    const preloadLinks = [
        '/blog',
        '/reports',
        '/aboutme'
    ];
    
    preloadLinks.forEach(href => {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = href;
        document.head.appendChild(link);
    });
    
    // 移除加载状态
    document.body.classList.remove('loading');
}); 