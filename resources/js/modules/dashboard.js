// Dashboard 模块 - 仪表板页面功能

/**
 * 仪表板功能模块
 */
class DashboardModule {
    constructor() {
        this.charts = new Map();
        this.refreshInterval = null;
        this.config = {
            refreshRate: 30000, // 30秒刷新
            animationDuration: 750
        };
    }

    /**
     * 初始化模块
     */
    async init() {
        console.log('📊 Dashboard 模块已加载');
        
        this.initializeCharts();
        this.setupRefreshTimer();
        this.bindEvents();
    }

    /**
     * 初始化图表
     */
    initializeCharts() {
        // 统计卡片动画
        this.animateStatCards();
        
        // 访问量趋势图
        this.initVisitTrendChart();
        
        // 文章分类饼图
        this.initCategoryChart();
        
        // 最近访问活动图
        this.initActivityChart();
    }

    /**
     * 统计卡片动画
     */
    animateStatCards() {
        const statCards = document.querySelectorAll('.stat-card');
        
        statCards.forEach((card, index) => {
            const counter = card.querySelector('.stat-number');
            if (counter) {
                const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
                this.animateCounter(counter, target, index * 200);
            }
        });
    }

    /**
     * 数字动画
     */
    animateCounter(element, target, delay = 0) {
        setTimeout(() => {
            const duration = 2000;
            const start = 0;
            const startTime = performance.now();

            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                const current = Math.floor(start + (target - start) * easeOutQuart);
                
                element.textContent = this.formatNumber(current);
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };

            requestAnimationFrame(animate);
        }, delay);
    }

    /**
     * 格式化数字
     */
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }

    /**
     * 初始化访问趋势图
     */
    initVisitTrendChart() {
        const canvas = document.getElementById('visitTrendChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.getLast7Days(),
                datasets: [{
                    label: '页面访问量',
                    data: this.getVisitData(),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: this.config.animationDuration,
                    easing: 'easeInOutQuart'
                }
            }
        });

        this.charts.set('visitTrend', chart);
    }

    /**
     * 初始化分类图表
     */
    initCategoryChart() {
        const canvas = document.getElementById('categoryChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['技术', '生活', '随笔', '教程', '其他'],
                datasets: [{
                    data: [30, 25, 20, 15, 10],
                    backgroundColor: [
                        '#4f46e5',
                        '#06b6d4',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                animation: {
                    duration: this.config.animationDuration,
                    animateRotate: true,
                    animateScale: true
                }
            }
        });

        this.charts.set('category', chart);
    }

    /**
     * 初始化活动图表
     */
    initActivityChart() {
        const canvas = document.getElementById('activityChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
                datasets: [{
                    label: '活跃度',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    borderColor: '#4f46e5',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: this.config.animationDuration,
                    easing: 'easeInOutQuart'
                }
            }
        });

        this.charts.set('activity', chart);
    }

    /**
     * 获取最近7天的日期
     */
    getLast7Days() {
        const days = [];
        for (let i = 6; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            days.push(date.getMonth() + 1 + '/' + date.getDate());
        }
        return days;
    }

    /**
     * 获取访问数据（模拟数据）
     */
    getVisitData() {
        return [120, 190, 300, 500, 200, 300, 450];
    }

    /**
     * 设置刷新定时器
     */
    setupRefreshTimer() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }

        this.refreshInterval = setInterval(() => {
            this.refreshData();
        }, this.config.refreshRate);
    }

    /**
     * 刷新数据
     */
    async refreshData() {
        try {
            // 更新统计数据
            await this.updateStats();
            
            // 更新图表数据
            this.updateCharts();
            
            console.log('📊 Dashboard 数据已刷新');
        } catch (error) {
            console.error('Dashboard 数据刷新失败:', error);
        }
    }

    /**
     * 更新统计数据
     */
    async updateStats() {
        // 这里可以发送 AJAX 请求获取最新数据
        const response = await fetch('/admin/api/stats');
        if (response.ok) {
            const stats = await response.json();
            this.updateStatCards(stats);
        }
    }

    /**
     * 更新统计卡片
     */
    updateStatCards(stats) {
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach(card => {
            const type = card.dataset.type;
            const counter = card.querySelector('.stat-number');
            
            if (stats[type] && counter) {
                const newValue = stats[type];
                const currentValue = parseInt(counter.textContent.replace(/[^\d]/g, ''));
                
                if (newValue !== currentValue) {
                    this.animateCounter(counter, newValue);
                    
                    // 添加更新动画
                    card.classList.add('updated');
                    setTimeout(() => {
                        card.classList.remove('updated');
                    }, 1000);
                }
            }
        });
    }

    /**
     * 更新图表
     */
    updateCharts() {
        // 更新访问趋势图
        const visitChart = this.charts.get('visitTrend');
        if (visitChart) {
            visitChart.data.datasets[0].data = this.getVisitData();
            visitChart.update('active');
        }
    }

    /**
     * 绑定事件
     */
    bindEvents() {
        // 手动刷新按钮
        const refreshBtn = document.getElementById('refreshDashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                refreshBtn.classList.add('spinning');
                this.refreshData().finally(() => {
                    setTimeout(() => {
                        refreshBtn.classList.remove('spinning');
                    }, 1000);
                });
            });
        }

        // 时间范围选择
        const timeRange = document.getElementById('timeRange');
        if (timeRange) {
            timeRange.addEventListener('change', (e) => {
                this.updateTimeRange(e.target.value);
            });
        }
    }

    /**
     * 更新时间范围
     */
    updateTimeRange(range) {
        console.log('时间范围更新:', range);
        // 根据选择的时间范围更新图表数据
        this.updateCharts();
    }

    /**
     * 销毁模块
     */
    destroy() {
        // 清理定时器
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }

        // 销毁图表
        this.charts.forEach(chart => {
            chart.destroy();
        });
        this.charts.clear();

        console.log('📊 Dashboard 模块已销毁');
    }
}

// 导出模块
export default {
    init: () => {
        if (!window.dashboardModule) {
            window.dashboardModule = new DashboardModule();
        }
        return window.dashboardModule.init();
    },
    
    destroy: () => {
        if (window.dashboardModule) {
            window.dashboardModule.destroy();
            window.dashboardModule = null;
        }
    }
}; 