// Charts 模块 - 通用图表功能

/**
 * 图表工具类
 */
class ChartsModule {
    constructor() {
        this.defaultColors = [
            '#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444',
            '#8b5cf6', '#ec4899', '#f97316', '#84cc16', '#6b7280'
        ];
        
        this.defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 750,
                easing: 'easeInOutQuart'
            }
        };
    }

    /**
     * 初始化模块
     */
    async init() {
        console.log('📈 Charts 模块已加载');
        
        // 设置 Chart.js 默认配置
        this.setupChartDefaults();
    }

    /**
     * 设置 Chart.js 默认配置
     */
    setupChartDefaults() {
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            Chart.defaults.color = '#6b7280';
            Chart.defaults.borderColor = 'rgba(0, 0, 0, 0.1)';
        }
    }

    /**
     * 创建折线图
     */
    createLineChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.warn(`Canvas ${canvasId} not found`);
            return null;
        }

        const ctx = canvas.getContext('2d');
        const defaultConfig = {
            type: 'line',
            data: config.data,
            options: {
                ...this.defaultOptions,
                ...config.options,
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
                    },
                    ...config.options?.scales
                }
            }
        };

        return new Chart(ctx, defaultConfig);
    }

    /**
     * 创建柱状图
     */
    createBarChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.warn(`Canvas ${canvasId} not found`);
            return null;
        }

        const ctx = canvas.getContext('2d');
        const defaultConfig = {
            type: 'bar',
            data: config.data,
            options: {
                ...this.defaultOptions,
                ...config.options,
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
                    },
                    ...config.options?.scales
                }
            }
        };

        return new Chart(ctx, defaultConfig);
    }

    /**
     * 创建饼图
     */
    createPieChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.warn(`Canvas ${canvasId} not found`);
            return null;
        }

        const ctx = canvas.getContext('2d');
        const defaultConfig = {
            type: 'pie',
            data: {
                ...config.data,
                datasets: config.data.datasets.map(dataset => ({
                    ...dataset,
                    backgroundColor: dataset.backgroundColor || this.defaultColors.slice(0, dataset.data.length),
                    borderWidth: dataset.borderWidth || 0,
                    hoverOffset: dataset.hoverOffset || 10
                }))
            },
            options: {
                ...this.defaultOptions,
                ...config.options,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    ...config.options?.plugins
                }
            }
        };

        return new Chart(ctx, defaultConfig);
    }

    /**
     * 创建环形图
     */
    createDoughnutChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.warn(`Canvas ${canvasId} not found`);
            return null;
        }

        const ctx = canvas.getContext('2d');
        const defaultConfig = {
            type: 'doughnut',
            data: {
                ...config.data,
                datasets: config.data.datasets.map(dataset => ({
                    ...dataset,
                    backgroundColor: dataset.backgroundColor || this.defaultColors.slice(0, dataset.data.length),
                    borderWidth: dataset.borderWidth || 0,
                    hoverOffset: dataset.hoverOffset || 10
                }))
            },
            options: {
                ...this.defaultOptions,
                ...config.options,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    ...config.options?.plugins
                }
            }
        };

        return new Chart(ctx, defaultConfig);
    }

    /**
     * 创建面积图
     */
    createAreaChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.warn(`Canvas ${canvasId} not found`);
            return null;
        }

        const ctx = canvas.getContext('2d');
        const defaultConfig = {
            type: 'line',
            data: {
                ...config.data,
                datasets: config.data.datasets.map(dataset => ({
                    ...dataset,
                    fill: true,
                    backgroundColor: dataset.backgroundColor || this.hexToRgba(dataset.borderColor || this.defaultColors[0], 0.1),
                    tension: dataset.tension || 0.4
                }))
            },
            options: {
                ...this.defaultOptions,
                ...config.options,
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
                    },
                    ...config.options?.scales
                }
            }
        };

        return new Chart(ctx, defaultConfig);
    }

    /**
     * 更新图表数据
     */
    updateChart(chart, newData) {
        if (!chart) return;

        chart.data.labels = newData.labels || chart.data.labels;
        chart.data.datasets.forEach((dataset, index) => {
            if (newData.datasets[index]) {
                dataset.data = newData.datasets[index].data;
                if (newData.datasets[index].label) {
                    dataset.label = newData.datasets[index].label;
                }
            }
        });

        chart.update('active');
    }

    /**
     * 销毁图表
     */
    destroyChart(chart) {
        if (chart && typeof chart.destroy === 'function') {
            chart.destroy();
        }
    }

    /**
     * 获取响应式图表选项
     */
    getResponsiveOptions(breakpoints = {}) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: window.innerWidth <= (breakpoints.mobile || 768) ? 'bottom' : 'top',
                    labels: {
                        boxWidth: window.innerWidth <= (breakpoints.mobile || 768) ? 12 : 15,
                        padding: window.innerWidth <= (breakpoints.mobile || 768) ? 15 : 20
                    }
                }
            }
        };
    }

    /**
     * 十六进制颜色转 RGBA
     */
    hexToRgba(hex, alpha = 1) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        if (!result) return hex;

        const r = parseInt(result[1], 16);
        const g = parseInt(result[2], 16);
        const b = parseInt(result[3], 16);

        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    /**
     * 生成渐变背景
     */
    createGradient(ctx, color1, color2, direction = 'vertical') {
        const gradient = direction === 'vertical'
            ? ctx.createLinearGradient(0, 0, 0, 400)
            : ctx.createLinearGradient(0, 0, 400, 0);

        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);

        return gradient;
    }

    /**
     * 获取图表主题颜色
     */
    getThemeColors(theme = 'default') {
        const themes = {
            default: this.defaultColors,
            blue: ['#3b82f6', '#1d4ed8', '#1e40af', '#1e3a8a', '#1f2937'],
            green: ['#10b981', '#059669', '#047857', '#065f46', '#064e3b'],
            purple: ['#8b5cf6', '#7c3aed', '#6d28d9', '#5b21b6', '#4c1d95'],
            orange: ['#f97316', '#ea580c', '#dc2626', '#b91c1c', '#991b1b']
        };

        return themes[theme] || themes.default;
    }

    /**
     * 导出图表为图片
     */
    exportChart(chart, filename = 'chart.png') {
        if (!chart || !chart.canvas) return;

        const url = chart.canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = filename;
        link.href = url;
        link.click();
    }

    /**
     * 获取图表数据点信息
     */
    getDataPointInfo(chart, event) {
        if (!chart) return null;

        const points = Chart.getElementsAtEventForMode(chart, event, 'nearest', { intersect: true }, true);
        
        if (points.length) {
            const point = points[0];
            const datasetIndex = point.datasetIndex;
            const index = point.index;
            
            return {
                datasetIndex,
                index,
                dataset: chart.data.datasets[datasetIndex],
                label: chart.data.labels[index],
                value: chart.data.datasets[datasetIndex].data[index]
            };
        }

        return null;
    }
}

// 创建全局实例
const chartsModule = new ChartsModule();

// 导出模块
export default {
    init: () => chartsModule.init(),
    
    createLineChart: (canvasId, config) => chartsModule.createLineChart(canvasId, config),
    createBarChart: (canvasId, config) => chartsModule.createBarChart(canvasId, config),
    createPieChart: (canvasId, config) => chartsModule.createPieChart(canvasId, config),
    createDoughnutChart: (canvasId, config) => chartsModule.createDoughnutChart(canvasId, config),
    createAreaChart: (canvasId, config) => chartsModule.createAreaChart(canvasId, config),
    
    updateChart: (chart, newData) => chartsModule.updateChart(chart, newData),
    destroyChart: (chart) => chartsModule.destroyChart(chart),
    exportChart: (chart, filename) => chartsModule.exportChart(chart, filename),
    
    getThemeColors: (theme) => chartsModule.getThemeColors(theme),
    getResponsiveOptions: (breakpoints) => chartsModule.getResponsiveOptions(breakpoints),
    getDataPointInfo: (chart, event) => chartsModule.getDataPointInfo(chart, event),
    
    // 直接暴露工具函数
    hexToRgba: (hex, alpha) => chartsModule.hexToRgba(hex, alpha),
    createGradient: (ctx, color1, color2, direction) => chartsModule.createGradient(ctx, color1, color2, direction)
}; 