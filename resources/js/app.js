// Laravel Report System with Vue.js
import { createApp } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { useElementVisibility } from '@vueuse/core';


// 注册GSAP插件
gsap.registerPlugin(ScrollTrigger);

// Vue应用程序
const app = createApp({
    data() {
        return {
            searchTerm: '',
            reports: [],
            isLoading: false,
            showScrollTop: false,
            darkMode: false,
            particles: [],
            audioContext: null,
            analyser: null,
            dataArray: null,
            animationConfig: {
                duration: 0.8,
                ease: 'power2.out'
            }
        };
    },
    mounted() {
        this.initializeApp();
        this.setupScrollObserver();
        this.animateOnLoad();
        this.initAllEffects();
        this.syncThemeState();
    },
    methods: {
        initializeApp() {
            // 初始化报告数据
            this.loadReports();
    
    // 初始化代码高亮
            this.initCodeHighlight();
            
            // 监听滚动
            this.setupScrollListener();
            
            console.log('Vue.js Report System initialized');
        },
        
        loadReports() {
            const reportCards = document.querySelectorAll('.report-card');
            this.reports = Array.from(reportCards).map(card => ({
                element: card,
                title: card.querySelector('.title-text')?.textContent?.toLowerCase() || 
                       card.querySelector('.report-title')?.textContent?.toLowerCase() || '',
                meta: card.querySelector('.report-meta')?.textContent?.toLowerCase() || '',
                visible: true
            }));
        },
        
        animateOnLoad() {
            // 动画导航栏
            gsap.fromTo('.navbar', {
                y: -100,
                opacity: 0
            }, {
                y: 0,
                opacity: 1,
                duration: this.animationConfig.duration,
                ease: this.animationConfig.ease
            });
            
            // 动画报告卡片 (仅在元素存在时执行)
            const reportCards = document.querySelectorAll('.report-card');
            if (reportCards.length > 0) {
                gsap.fromTo('.report-card', {
                    y: 50,
                    opacity: 0,
                    scale: 0.9
                }, {
                    y: 0,
                    opacity: 1,
                    scale: 1,
                    duration: this.animationConfig.duration,
                    ease: this.animationConfig.ease,
                    stagger: 0.1
                });
            }
            
            // 动画搜索框 (仅在元素存在时执行)
            const searchElement = document.querySelector('#report-search');
            if (searchElement) {
                gsap.fromTo('#report-search', {
                    x: -50,
                    opacity: 0
                }, {
                    x: 0,
                    opacity: 1,
                    duration: this.animationConfig.duration,
                    ease: this.animationConfig.ease,
                    delay: 0.3
                });
            }
        },
        
        setupScrollObserver() {
            // 使用GSAP ScrollTrigger监听滚动
            ScrollTrigger.create({
                trigger: 'body',
                start: 'top -100px',
                end: 'bottom bottom',
                onUpdate: self => {
                    this.showScrollTop = self.direction === 1 && self.progress > 0.1;
                }
            });
        },
        
        setupScrollListener() {
            let ticking = false;
            
            window.addEventListener('scroll', () => {
                if (!ticking) {
                    requestAnimationFrame(() => {
                        const scrollY = window.scrollY;
                        
                        // 视差效果
                        const parallaxElements = document.querySelectorAll('.parallax');
                        parallaxElements.forEach(element => {
                            const speed = element.dataset.speed || 0.5;
                            const transform = `translateY(${scrollY * speed}px)`;
                            element.style.transform = transform;
                        });
                        
                        ticking = false;
                    });
                    ticking = true;
                }
            });
        },
        
        filterReports() {
            const searchTerm = this.searchTerm.toLowerCase();
            
            this.reports.forEach(report => {
                const isVisible = report.title.includes(searchTerm) || 
                                report.meta.includes(searchTerm);
                
                if (isVisible !== report.visible) {
                    report.visible = isVisible;
                    
                    // 动画显示/隐藏
                    gsap.to(report.element, {
                        opacity: isVisible ? 1 : 0,
                        y: isVisible ? 0 : -20,
                        scale: isVisible ? 1 : 0.9,
                        duration: 0.3,
                        ease: 'power2.out',
                        onComplete: () => {
                            report.element.style.display = isVisible ? 'block' : 'none';
                        }
                    });
                }
            });
        },
        
                initCodeHighlight() {
            // 代码高亮功能已集成到 report.js 中的 initCodeCopy() 函数
            // 这里不再需要额外的复制按钮
        },


        
        smoothScrollTo(e, targetId) {
            e.preventDefault();
    const target = document.getElementById(targetId);
    if (target) {
                gsap.to(window, {
                    scrollTo: target,
                    duration: 0.8,
                    ease: 'power2.out'
                });
            }
        },
        
        scrollToTop() {
            gsap.to(window, {
                scrollTo: 0,
                duration: 0.8,
                ease: 'power2.out'
            });
        },
        
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        },
        
        // 初始化粒子效果
        initParticles() {
            const container = document.querySelector('.particles-container');
            if (!container) return;
            
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                container.appendChild(particle);
            }
        },
        
        // 初始化页面加载动画
        initPageLoader() {
            const loader = document.createElement('div');
            loader.className = 'page-loader';
            loader.innerHTML = `
                <div class="loader-content">
                    <div class="loader-spinner"></div>
                    <div class="loader-text">Loading Awesome Content...</div>
                </div>
            `;
            document.body.appendChild(loader);
            
            // 2秒后隐藏加载动画
            setTimeout(() => {
                loader.classList.add('hidden');
                setTimeout(() => {
                    loader.remove();
                }, 500);
            }, 2000);
        },
        
        // 添加涟漪效果
        createRipple(event) {
            const button = event.target.closest('.btn, .nav-btn, .report-card') || event.target;
            if (!button || typeof button.getBoundingClientRect !== 'function') {
                return; // 如果不是有效的DOM元素，直接返回
            }
            
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = event.clientX - rect.left - size / 2;
            const y = event.clientY - rect.top - size / 2;
            
            const ripple = document.createElement('div');
            ripple.className = 'ripple';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            // 确保button能够包含ripple元素
            if (button && button.appendChild) {
                button.appendChild(ripple);
                
                setTimeout(() => {
                    if (ripple.parentNode) {
                        ripple.parentNode.removeChild(ripple);
                    }
                }, 600);
            }
        },
        
        // 初始化3D卡片效果
        init3DCards() {
            const cards = document.querySelectorAll('.report-card');
            
            cards.forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    const rotateX = (y - centerY) / 10;
                    const rotateY = (centerX - x) / 10;
                    
                    card.style.transform = `
                        translateY(-10px) 
                        rotateX(${rotateX}deg) 
                        rotateY(${rotateY}deg)
                        scale(1.02)
                    `;
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0) rotateX(0) rotateY(0) scale(1)';
                });
            });
        },
        
        // 初始化打字机效果
        initTypewriter() {
            const titleElement = document.querySelector('.page-title');
            if (!titleElement) return;
            
            const originalText = titleElement.textContent;
            titleElement.textContent = '';
            
            let i = 0;
            const typeInterval = setInterval(() => {
                if (i < originalText.length) {
                    titleElement.textContent += originalText.charAt(i);
                    i++;
                } else {
                    clearInterval(typeInterval);
                }
            }, 100);
        },
        
        // 初始化音频可视化
        initAudioVisualization() {
            // 创建音频上下文
            try {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                this.analyser = this.audioContext.createAnalyser();
                this.analyser.fftSize = 256;
                this.dataArray = new Uint8Array(this.analyser.frequencyBinCount);
                
                // 连接到音频源（如果有的话）
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then(stream => {
                        const source = this.audioContext.createMediaStreamSource(stream);
                        source.connect(this.analyser);
                        this.visualizeAudio();
                    })
                    .catch(err => {
                        console.log('Audio access denied:', err);
                    });
            } catch (err) {
                console.log('Audio not supported:', err);
            }
        },
        
        // 音频可视化
        visualizeAudio() {
            const canvas = document.createElement('canvas');
            canvas.width = window.innerWidth;
            canvas.height = 100;
            canvas.style.position = 'fixed';
            canvas.style.bottom = '0';
            canvas.style.left = '0';
            canvas.style.pointerEvents = 'none';
            canvas.style.zIndex = '9999';
            canvas.style.opacity = '0.5';
            document.body.appendChild(canvas);
            
            const ctx = canvas.getContext('2d');
            
            const animate = () => {
                if (this.analyser) {
                    this.analyser.getByteFrequencyData(this.dataArray);
                    
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    
                    const barWidth = canvas.width / this.dataArray.length;
                    
                    for (let i = 0; i < this.dataArray.length; i++) {
                        const barHeight = this.dataArray[i] / 2;
                        const hue = i * 360 / this.dataArray.length;
                        
                        ctx.fillStyle = `hsl(${hue}, 100%, 50%)`;
                        ctx.fillRect(i * barWidth, canvas.height - barHeight, barWidth, barHeight);
                    }
                }
                
                requestAnimationFrame(animate);
            };
            animate();
        },
        
        // 初始化视差滚动
        initParallaxScroll() {
            const parallaxElements = document.querySelectorAll('.report-card');
            
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.1;
                
                parallaxElements.forEach((element, index) => {
                    const yPos = -(scrolled * (0.1 + index * 0.02));
                    element.style.transform = `translateY(${yPos}px)`;
                });
            });
        },
        
        // 初始化彩虹色文字动画
        initRainbowText() {
            const rainbowElements = document.querySelectorAll('.page-title, .navbar h1 a');
            
            rainbowElements.forEach(element => {
                element.style.backgroundSize = '400% 400%';
                element.style.animation = 'rainbow-text 3s ease infinite';
            });
        },
        
        // 初始化悬浮动画
        initFloatingAnimation() {
            const floatingElements = document.querySelectorAll('.stat-item');
            
            floatingElements.forEach((element, index) => {
                element.style.animation = `float-gentle ${3 + index * 0.5}s ease-in-out infinite`;
                element.style.animationDelay = `${index * 0.2}s`;
            });
        },
        
        // 初始化粒子碰撞效果
        initParticleCollision() {
            const particles = document.querySelectorAll('.particle');
            
            particles.forEach(particle => {
                particle.addEventListener('animationiteration', () => {
                    // 随机改变粒子颜色
                    const colors = ['--neon-blue', '--neon-purple', '--neon-pink', '--neon-green', '--neon-orange'];
                    const randomColor = colors[Math.floor(Math.random() * colors.length)];
                    particle.style.background = `var(${randomColor})`;
                    particle.style.boxShadow = `0 0 10px var(${randomColor})`;
                });
            });
        },
        
        // 初始化键盘快捷键
        initKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Ctrl/Cmd + K 搜索
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    const searchInput = document.getElementById('report-search');
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }
                }
                
                // Ctrl/Cmd + D 切换暗黑模式
                if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                    e.preventDefault();
                    this.toggleDarkMode();
                }
                
                // F11 全屏
                if (e.key === 'F11') {
                    e.preventDefault();
                    this.toggleFullscreen();
                }
                
                // Escape 退出全屏
                if (e.key === 'Escape' && document.fullscreenElement) {
                    document.exitFullscreen();
                }
                
                // 空格键暂停/恢复动画
                if (e.key === ' ' && e.target.tagName !== 'INPUT') {
                    e.preventDefault();
                    this.toggleAnimations();
                }
            });
        },
        
        // 切换动画
        toggleAnimations() {
            const body = document.body;
            body.classList.toggle('animations-paused');
            
            if (body.classList.contains('animations-paused')) {
                body.style.animationPlayState = 'paused';
                document.querySelectorAll('*').forEach(el => {
                    el.style.animationPlayState = 'paused';
                });
            } else {
                body.style.animationPlayState = 'running';
                document.querySelectorAll('*').forEach(el => {
                    el.style.animationPlayState = 'running';
                });
            }
        },
        
        // 同步主题状态
        syncThemeState() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            this.darkMode = currentTheme === 'dark';
        },
        
        // 切换暗黑模式
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            if (this.darkMode) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme-mode', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme-mode', 'light');
            }
        },
        
        // 初始化所有特效
        initAllEffects() {
            // 创建粒子容器
            const particlesContainer = document.createElement('div');
            particlesContainer.className = 'particles-container';
            particlesContainer.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 1;
            `;
            document.body.appendChild(particlesContainer);
            
            // 初始化各种特效
            // this.initPageLoader(); // 已注释掉加载动画
            this.initParticles();
            this.init3DCards();
            this.initTypewriter();
            this.initRainbowText();
            this.initFloatingAnimation();
            this.initKeyboardShortcuts();
            // this.initAudioVisualization(); // 可选：音频可视化
            
            // 添加涟漪效果到所有按钮
            document.addEventListener('click', (e) => {
                if (e.target.matches('.btn, .nav-btn, .report-card')) {
                    this.createRipple(e);
                }
            });
            
            // 监听滚动事件
            window.addEventListener('scroll', () => {
                this.checkScrollPosition();
            });
            
            // 延迟初始化需要DOM完全加载的效果
            setTimeout(() => {
                this.initParticleCollision();
                this.init3DCards();
            }, 2500);
        },
        
        // 检查滚动位置
        checkScrollPosition() {
            this.showScrollTop = window.pageYOffset > 300;
        }
    },
    
    watch: {
        searchTerm: {
            handler: 'filterReports',
            immediate: false
        }
    },
    
    computed: {
        filteredReports() {
            if (!this.searchTerm) {
                return this.reports;
            }
            return this.reports.filter(report =>
                report.title.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                report.meta.toLowerCase().includes(this.searchTerm.toLowerCase())
            );
        },
        visibleReportsCount() {
            return this.filteredReports.length;
        }
    }
});

// 挂载Vue应用
app.mount('#app');

// 全局样式增强
document.addEventListener('DOMContentLoaded', function() {
    // 如果URL中有hash，平滑滚动到对应元素
    if (window.location.hash) {
        setTimeout(() => {
            const target = document.querySelector(window.location.hash);
            if (target) {
                gsap.to(window, {
                    scrollTo: target,
                    duration: 0.8,
                    ease: 'power2.out'
                });
            }
        }, 500);
    }
});

// 全局函数（用于Blade模板）
window.shareReport = function(slug) {
    const url = `${window.location.origin}/${slug}.html`;
    
    if (navigator.share) {
        navigator.share({
            title: '查看报告',
            text: '来看看这个有趣的报告',
            url: url
        });
    } else {
        // 复制到剪贴板
        navigator.clipboard.writeText(url).then(() => {
            // 显示炫酷的提示
            const toast = document.createElement('div');
            toast.innerHTML = '🎉 链接已复制到剪贴板！';
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(45deg, #667eea, #764ba2);
                color: white;
                padding: 15px 25px;
                border-radius: 10px;
                font-weight: 600;
                z-index: 10000;
                animation: slideInRight 0.5s ease;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.5s ease';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        });
    }
};

window.toggleFullscreen = function() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
};

// toggleDarkMode 函数已在 layout.js 中定义，这里移除重复定义

window.scrollToTop = function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};

// 添加动画样式
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    @keyframes rainbow-text {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    @keyframes float-gentle {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    /* 涟漪效果样式 */
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    /* 主题切换专用涟漪效果 */
    .theme-ripple {
        position: absolute;
        border-radius: 50%;
        transform: scale(0);
        animation: theme-ripple-animation 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
        z-index: 0;
    }
    
    @keyframes theme-ripple-animation {
        0% {
            transform: scale(0);
            opacity: 1;
        }
        50% {
            transform: scale(2);
            opacity: 0.8;
        }
        100% {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    /* 确保按钮和卡片可以包含绝对定位的元素 */
    .btn, .nav-btn, .report-card {
        position: relative;
        overflow: hidden;
    }
`;
document.head.appendChild(style);

// 添加一些额外的交互效果
document.addEventListener('DOMContentLoaded', () => {
    // 为所有链接添加悬浮效果
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('mouseenter', (e) => {
            e.target.style.transition = 'all 0.3s ease';
            e.target.style.transform = 'scale(1.05)';
        });
        
        link.addEventListener('mouseleave', (e) => {
            e.target.style.transform = 'scale(1)';
        });
    });
    
    // 为搜索框添加特殊效果
    const searchInput = document.getElementById('report-search');
    if (searchInput) {
        searchInput.addEventListener('focus', () => {
            searchInput.parentElement.style.boxShadow = '0 0 30px rgba(59, 130, 246, 0.5)';
        });
        
        searchInput.addEventListener('blur', () => {
            searchInput.parentElement.style.boxShadow = '';
        });
    }
});

console.log('🎨 炫酷特效已启动！');
console.log('⌨️ 快捷键：');
console.log('  Ctrl+K: 搜索');
console.log('  Ctrl+D: 切换暗黑模式');
console.log('  F11: 全屏');
console.log('  Space: 暂停/恢复动画');
console.log('  Escape: 退出全屏'); 