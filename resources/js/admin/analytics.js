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
        // 安全地解析JSON数据
        let trendDataText = trendDataElement.textContent.trim();
        
        // 移除可能的BOM字符
        if (trendDataText.charCodeAt(0) === 0xFEFF) {
            trendDataText = trendDataText.substr(1);
        }
        
        if (!trendDataText) {
            console.warn('No trend data found');
            ctx.parentElement.innerHTML = '<p style="text-align: center; color: #666; margin: 2rem 0;">No trend data available</p>';
            return;
        }
        
        // 验证JSON格式
        if (!trendDataText.startsWith('[') && !trendDataText.startsWith('{')) {
            console.error('Invalid JSON format:', trendDataText.substring(0, 50));
            ctx.parentElement.innerHTML = '<p style="text-align: center; color: #e74c3c; margin: 2rem 0;">Data format error</p>';
            return;
        }
        
        const trendData = JSON.parse(trendDataText);
        
        // 使用简单的HTML/CSS图表替代Chart.js
        createSimpleChart(ctx.parentElement, trendData);
        
    } catch (error) {
        console.error('Failed to initialize trend chart:', error);
        console.error('Raw data:', trendDataElement.textContent.substring(0, 100));
        
        // 显示友好的错误信息
        ctx.parentElement.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #e74c3c;">
                <p>Chart loading failed</p>
                <p style="font-size: 0.875rem; color: #666; margin-top: 0.5rem;">Please refresh the page and try again</p>
            </div>
        `;
    }
}

// 创建简单的HTML/CSS图表
function createSimpleChart(container, data) {
    if (!data || !Array.isArray(data) || data.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #666; margin: 2rem 0;">No data available</p>';
        return;
    }
    
    // 计算最大值用于缩放
    const maxPv = Math.max(...data.map(d => d.pv || 0));
    const maxUv = Math.max(...data.map(d => d.uv || 0));
    const maxValue = Math.max(maxPv, maxUv);
    
    if (maxValue === 0) {
        container.innerHTML = '<p style="text-align: center; color: #666; margin: 2rem 0;">No visit data available</p>';
        return;
    }
    
    let chartHtml = `
        <div class="simple-chart" style="padding: 1rem; background: white; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.875rem;">
                <div><span style="color: #3498db;">■</span> PV (Page Views)</div>
                <div><span style="color: #e74c3c;">■</span> UV (Unique Visitors)</div>
                <div style="color: #666;">Max value: ${maxValue.toLocaleString()}</div>
            </div>
            <div class="chart-bars" style="display: flex; align-items: end; gap: 2px; height: 200px; border-left: 1px solid #ddd; border-bottom: 1px solid #ddd; padding: 0 0 0 0;">
    `;
    
    data.forEach((item, index) => {
        const pvHeight = maxValue > 0 ? (item.pv / maxValue) * 180 : 0;
        const uvHeight = maxValue > 0 ? (item.uv / maxValue) * 180 : 0;
        
        chartHtml += `
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; position: relative;">
                <div style="display: flex; align-items: end; width: 100%; gap: 1px; justify-content: center;">
                    <div style="width: 40%; background: #3498db; height: ${pvHeight}px; min-height: 2px; opacity: 0.8;"></div>
                    <div style="width: 40%; background: #e74c3c; height: ${uvHeight}px; min-height: 2px; opacity: 0.8;"></div>
                </div>
                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #666; transform: rotate(-45deg); transform-origin: center;">
                    ${item.date}
                </div>
            </div>
        `;
    });
    
    chartHtml += `
            </div>
            <div style="margin-top: 1rem; text-align: center; font-size: 0.875rem; color: #666;">
                Traffic Trend (Last ${data.length} days)
            </div>
        </div>
    `;
    
    container.innerHTML = chartHtml;
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
            if (header.textContent.includes('Actions') || header.textContent.includes('操作')) return;
            
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
            this.textContent = 'Exporting...';
            this.disabled = true;
            
            // 模拟导出过程
            setTimeout(() => {
                this.textContent = originalText;
                this.disabled = false;
            }, 2000);
        });
    });
}

// 初始化实时图表
export function initRealtimeChart() {
    const ctx = document.getElementById('realtimeTrendChart');
    if (!ctx) return;
    
    const dataElement = document.getElementById('realtime-trend-data');
    if (!dataElement) return;
    
    try {
        let dataText = dataElement.textContent.trim();
        
        // 移除可能的BOM字符
        if (dataText.charCodeAt(0) === 0xFEFF) {
            dataText = dataText.substr(1);
        }
        
        if (!dataText) {
            console.warn('No realtime trend data found');
            ctx.parentElement.innerHTML = '<p style="text-align: center; color: #666; margin: 2rem 0;">No real-time data available</p>';
            return;
        }
        
        // 验证JSON格式
        if (!dataText.startsWith('[') && !dataText.startsWith('{')) {
            console.error('Invalid realtime JSON format:', dataText.substring(0, 50));
            ctx.parentElement.innerHTML = '<p style="text-align: center; color: #e74c3c; margin: 2rem 0;">Real-time data format error</p>';
            return;
        }
        
        const realtimeData = JSON.parse(dataText);
        
        // 使用简单的HTML/CSS图表替代Chart.js
        createRealtimeChart(ctx.parentElement, realtimeData);
        
    } catch (error) {
        console.error('Failed to initialize realtime chart:', error);
        console.error('Raw realtime data:', dataElement.textContent.substring(0, 100));
        
        ctx.parentElement.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #e74c3c;">
                <p>Real-time chart loading failed</p>
                <p style="font-size: 0.875rem; color: #666; margin-top: 0.5rem;">Please refresh the page and try again</p>
            </div>
        `;
    }
}

// 创建实时图表
function createRealtimeChart(container, data) {
    if (!data || !Array.isArray(data) || data.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #666; margin: 2rem 0;">No real-time data available</p>';
        return;
    }
    
    const maxValue = Math.max(...data.map(d => Math.max(d.pv || 0, d.uv || 0)));
    
    if (maxValue === 0) {
        container.innerHTML = '<p style="text-align: center; color: #666; margin: 2rem 0;">No real-time visit data available</p>';
        return;
    }
    
    let chartHtml = `
        <div class="realtime-chart" style="padding: 1rem; background: white; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.875rem;">
                <div><span style="color: #3498db;">■</span> PV</div>
                <div><span style="color: #e74c3c;">■</span> UV</div>
                <div style="color: #666;">24-hour trend</div>
            </div>
            <div class="realtime-bars" style="display: flex; align-items: end; gap: 1px; height: 150px; border-left: 1px solid #ddd; border-bottom: 1px solid #ddd;">
    `;
    
    data.forEach((item, index) => {
        const pvHeight = maxValue > 0 ? (item.pv / maxValue) * 130 : 0;
        const uvHeight = maxValue > 0 ? (item.uv / maxValue) * 130 : 0;
        
        chartHtml += `
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; position: relative;">
                <div style="display: flex; align-items: end; width: 100%; gap: 0.5px; justify-content: center;">
                    <div style="width: 45%; background: #3498db; height: ${pvHeight}px; min-height: 1px; opacity: 0.8;"></div>
                    <div style="width: 45%; background: #e74c3c; height: ${uvHeight}px; min-height: 1px; opacity: 0.8;"></div>
                </div>
                ${index % 4 === 0 ? `<div style="margin-top: 0.25rem; font-size: 0.6rem; color: #666;">${item.hour}</div>` : ''}
            </div>
        `;
    });
    
    chartHtml += `
            </div>
        </div>
    `;
    
    container.innerHTML = chartHtml;
}

// 初始化所有analytics功能
export function initAnalyticsModule() {
    initDeviceStats();
    initTrendChart();
    initRealtimeChart();
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