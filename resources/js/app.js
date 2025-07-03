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
            animationConfig: {
                duration: 0.6,
                ease: 'power2.out'
            }
        };
    },
    mounted() {
        this.initializeApp();
        this.setupScrollObserver();
        this.animateOnLoad();
    },
    methods: {
        initializeApp() {
            // 初始化报告数据
            this.loadReports();
            
            // 初始化代码高亮
            this.initCodeHighlight();
            
            // 初始化目录生成
            this.initTableOfContents();
            
            // 监听滚动
            this.setupScrollListener();
            
            console.log('Vue.js Report System initialized');
        },
        
        loadReports() {
            const reportCards = document.querySelectorAll('.report-card');
            this.reports = Array.from(reportCards).map(card => ({
                element: card,
                title: card.querySelector('.report-title')?.textContent?.toLowerCase() || '',
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
            
            // 动画报告卡片
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
            
            // 动画搜索框
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
            const codeBlocks = document.querySelectorAll('pre code');
            
            codeBlocks.forEach(block => {
                const pre = block.parentElement;
                const button = document.createElement('button');
                button.textContent = '复制';
                button.className = 'copy-btn';
                button.onclick = () => this.copyCode(block, button);
                
                pre.style.position = 'relative';
                pre.appendChild(button);
                
                // 添加动画
                gsap.fromTo(button, {
                    opacity: 0,
                    scale: 0.8
                }, {
                    opacity: 1,
                    scale: 1,
                    duration: 0.3,
                    ease: 'back.out(1.7)'
                });
            });
        },
        
        copyCode(block, button) {
            navigator.clipboard.writeText(block.textContent).then(() => {
                const originalText = button.textContent;
                button.textContent = '已复制!';
                
                // 动画反馈
                gsap.to(button, {
                    scale: 1.1,
                    duration: 0.1,
                    yoyo: true,
                    repeat: 1,
                    ease: 'power2.out'
                });
                
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            });
        },
        
        initTableOfContents() {
            const content = document.querySelector('.report-content');
            const tocContainer = document.getElementById('table-of-contents');
            
            if (content && tocContainer) {
                const headings = content.querySelectorAll('h1, h2, h3, h4, h5, h6');
                
                if (headings.length > 0) {
                    const toc = document.createElement('ul');
                    toc.className = 'toc-list';
                    
                    headings.forEach((heading, index) => {
                        if (!heading.id) {
                            heading.id = `heading-${index}`;
                        }
                        
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.href = `#${heading.id}`;
                        a.textContent = heading.textContent;
                        a.className = `toc-${heading.tagName.toLowerCase()}`;
                        a.onclick = (e) => this.smoothScrollTo(e, heading.id);
                        
                        li.appendChild(a);
                        toc.appendChild(li);
                    });
                    
                    tocContainer.appendChild(toc);
                    
                    // 动画目录
                    gsap.fromTo('.toc-list li', {
                        x: -30,
                        opacity: 0
                    }, {
                        x: 0,
                        opacity: 1,
                        duration: 0.4,
                        ease: 'power2.out',
                        stagger: 0.05
                    });
                }
            }
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
        }
    },
    
    watch: {
        searchTerm: {
            handler: 'filterReports',
            immediate: false
        }
    },
    
    computed: {
        visibleReportsCount() {
            return this.reports.filter(report => report.visible).length;
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