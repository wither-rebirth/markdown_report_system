// Analytics 模块 JavaScript 功能

// 时间周期切换
export function changePeriod(period) {
    const url = new URL(window.location);
    url.searchParams.set('period', period);
    window.location.href = url.toString();
}

// 数据导出
export function exportData(type) {
    const period = document.getElementById('period-select').value;
    // 注意：这里的路由需要在实际使用时替换为正确的路由
    window.location.href = `/admin/analytics/export?type=${type}&period=${period}&format=csv`;
}

// 初始化趋势图表
export function initTrendChart() {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;
    
    const trendDataElement = document.getElementById('trend-data');
    if (!trendDataElement) return;
    
    try {
        const trendData = JSON.parse(trendDataElement.textContent);
        
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'PV',
                    data: trendData.map(d => d.pv),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    fill: true
                }, {
                    label: 'UV',
                    data: trendData.map(d => d.uv),
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    } catch (error) {
        console.error('Failed to initialize trend chart:', error);
    }
}

// 初始化设备统计条形图
export function initDeviceStats() {
    document.querySelectorAll('.device-fill').forEach(function(element) {
        const width = element.getAttribute('data-width');
        if (width) {
            element.style.width = width + '%';
        }
    });
}

// 实时数据更新功能
export function initRealtimeUpdate() {
    const realtimeBtn = document.querySelector('a[href*="realtime"]');
    if (!realtimeBtn) return;
    
    // 为实时数据按钮添加动画效果
    realtimeBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.05)';
    });
    
    realtimeBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
}

// 统计卡片动画效果
export function initStatCards() {
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        });
    });
}

// 表格排序功能
export function initTableSorting() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(function(table) {
        const headers = table.querySelectorAll('th');
        
        headers.forEach(function(header, index) {
            // 跳过操作列
            if (header.textContent.includes('操作')) return;
            
            header.style.cursor = 'pointer';
            header.style.position = 'relative';
            
            header.addEventListener('click', function() {
                sortTable(table, index);
            });
        });
    });
}

function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const isNumeric = rows.some(row => {
        const cell = row.cells[columnIndex];
        return cell && !isNaN(parseFloat(cell.textContent.replace(/[,\s]/g, '')));
    });
    
    rows.sort((a, b) => {
        const aVal = a.cells[columnIndex]?.textContent.trim() || '';
        const bVal = b.cells[columnIndex]?.textContent.trim() || '';
        
        if (isNumeric) {
            return parseFloat(bVal.replace(/[,\s]/g, '')) - parseFloat(aVal.replace(/[,\s]/g, ''));
        } else {
            return bVal.localeCompare(aVal);
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// 导出按钮增强
export function enhanceExportButtons() {
    const exportButtons = document.querySelectorAll('.export-buttons .btn');
    
    exportButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const originalText = this.textContent;
            this.textContent = '导出中...';
            this.disabled = true;
            
            // 模拟导出过程
            setTimeout(() => {
                this.textContent = originalText;
                this.disabled = false;
            }, 2000);
        });
    });
}

// 初始化所有analytics功能
export function initAnalyticsModule() {
    initDeviceStats();
    initTrendChart();
    initRealtimeUpdate();
    initStatCards();
    initTableSorting();
    enhanceExportButtons();
}

// 将函数暴露到全局作用域（用于内联事件处理器）
if (typeof window !== 'undefined') {
    window.changePeriod = changePeriod;
    window.exportData = exportData;
}

// 页面加载完成后自动初始化
document.addEventListener('DOMContentLoaded', function() {
    initAnalyticsModule();
}); 