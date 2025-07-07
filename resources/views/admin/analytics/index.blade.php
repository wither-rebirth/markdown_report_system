@extends('admin.layout')

@section('title', '数据分析')

@section('content')
<div class="analytics-page">
    <div class="page-header">
        <h1>数据分析</h1>
        <div class="header-actions">
            <div class="period-selector">
                <select id="period-select" onchange="changePeriod(this.value)">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>今天</option>
                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>昨天</option>
                    <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>最近7天</option>
                    <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>最近30天</option>
                    <option value="90days" {{ $period === '90days' ? 'selected' : '' }}>最近90天</option>
                </select>
            </div>
            <a href="{{ route('admin.analytics.realtime') }}" class="btn btn-primary">实时数据</a>
        </div>
    </div>

    <!-- 基础统计卡片 -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">👁️</div>
            <div class="stat-content">
                <h3>页面访问量 (PV)</h3>
                <div class="stat-number">{{ number_format($basicStats['total_pv']) }}</div>
                @if(isset($basicStats['previous']['total_pv']))
                    @php
                        $pvChange = $basicStats['previous']['total_pv'] > 0 
                            ? (($basicStats['total_pv'] - $basicStats['previous']['total_pv']) / $basicStats['previous']['total_pv']) * 100 
                            : 0;
                    @endphp
                    <div class="stat-change {{ $pvChange >= 0 ? 'positive' : 'negative' }}">
                        {{ $pvChange >= 0 ? '+' : '' }}{{ number_format($pvChange, 1) }}%
                    </div>
                @endif
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>独立访客 (UV)</h3>
                <div class="stat-number">{{ number_format($basicStats['total_uv']) }}</div>
                @if(isset($basicStats['previous']['total_uv']))
                    @php
                        $uvChange = $basicStats['previous']['total_uv'] > 0 
                            ? (($basicStats['total_uv'] - $basicStats['previous']['total_uv']) / $basicStats['previous']['total_uv']) * 100 
                            : 0;
                    @endphp
                    <div class="stat-change {{ $uvChange >= 0 ? 'positive' : 'negative' }}">
                        {{ $uvChange >= 0 ? '+' : '' }}{{ number_format($uvChange, 1) }}%
                    </div>
                @endif
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🔄</div>
            <div class="stat-content">
                <h3>会话数</h3>
                <div class="stat-number">{{ number_format($basicStats['total_sessions']) }}</div>
                <div class="stat-sub">平均 {{ $basicStats['avg_pages_per_session'] }} 页/会话</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>跳出率</h3>
                <div class="stat-number">{{ $basicStats['bounce_rate'] }}%</div>
                <div class="stat-sub">新访客 {{ number_format($basicStats['new_visitors']) }}</div>
            </div>
        </div>
    </div>

    <!-- 趋势图表 -->
    <div class="chart-section">
        <div class="chart-container">
            <h3>访问趋势</h3>
            <canvas id="trendChart"></canvas>
            <script type="application/json" id="trend-data">{{ json_encode($trendData) }}</script>
        </div>
    </div>

    <!-- 详细统计 -->
    <div class="details-section">
        <div class="detail-card">
            <h3>热门页面</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>页面</th>
                            <th>访问量</th>
                            <th>独立访客</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topPages as $page)
                            <tr>
                                <td>
                                    <a href="{{ $page->url }}" target="_blank" class="page-link">
                                        {{ parse_url($page->url, PHP_URL_PATH) ?: '/' }}
                                    </a>
                                </td>
                                <td>{{ number_format($page->pv) }}</td>
                                <td>{{ number_format($page->uv) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="detail-card">
            <h3>设备统计</h3>
            <div class="device-stats">
                @foreach($deviceStats as $device)
                    <div class="device-item">
                        <span class="device-name">{{ $device->device_type }}</span>
                        <span class="device-count">{{ number_format($device->count) }}</span>
                        <div class="device-bar">
                            @php 
                                $maxCount = $deviceStats->max('count');
                                $percentage = $maxCount > 0 ? ($device->count / $maxCount) * 100 : 0;
                            @endphp
                            <div class="device-fill" data-width="{{ number_format($percentage, 1) }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="detail-card">
            <h3>浏览器统计</h3>
            <div class="browser-stats">
                @foreach($browserStats as $browser)
                    <div class="browser-item">
                        <span class="browser-name">{{ $browser->browser }}</span>
                        <span class="browser-count">{{ number_format($browser->count) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="detail-card">
            <h3>来源统计</h3>
            <div class="referer-stats">
                @if($refererStats->isEmpty())
                    <p class="text-muted">暂无来源数据</p>
                @else
                    @foreach($refererStats as $referer)
                        <div class="referer-item">
                            <span class="referer-name">{{ parse_url($referer->referer, PHP_URL_HOST) ?: '直接访问' }}</span>
                            <span class="referer-count">{{ number_format($referer->count) }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- 导出功能 -->
    <div class="export-section">
        <h3>数据导出</h3>
        <div class="export-buttons">
            <button onclick="exportData('visits')" class="btn btn-outline-primary">导出访问数据</button>
            <button onclick="exportData('daily_stats')" class="btn btn-outline-primary">导出统计数据</button>
            <button onclick="exportData('pages')" class="btn btn-outline-primary">导出页面数据</button>
        </div>
    </div>
</div>

<script>
// 时间周期切换
function changePeriod(period) {
    const url = new URL(window.location);
    url.searchParams.set('period', period);
    window.location.href = url.toString();
}

// 数据导出
function exportData(type) {
    const period = document.getElementById('period-select').value;
    window.location.href = `{{ route('admin.analytics.export') }}?type=${type}&period=${period}&format=csv`;
}

// 初始化页面
document.addEventListener('DOMContentLoaded', function() {
    // 设置设备条宽度
    document.querySelectorAll('.device-fill').forEach(function(element) {
        const width = element.getAttribute('data-width');
        element.style.width = width + '%';
    });
    
    // 绘制趋势图
    const ctx = document.getElementById('trendChart').getContext('2d');
    const trendData = JSON.parse(document.getElementById('trend-data').textContent);
    
    new Chart(ctx, {
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
});
</script>

<style>
.analytics-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header-actions {
    display: flex;
    gap: 15px;
    align-items: center;
}

.period-selector select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    font-size: 2rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
}

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    color: #666;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}

.stat-change {
    font-size: 0.8rem;
    font-weight: 500;
}

.stat-change.positive {
    color: #28a745;
}

.stat-change.negative {
    color: #dc3545;
}

.stat-sub {
    font-size: 0.8rem;
    color: #666;
    margin-top: 5px;
}

.chart-section {
    margin-bottom: 30px;
}

.chart-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chart-container h3 {
    margin: 0 0 20px 0;
    color: #333;
}

#trendChart {
    height: 400px !important;
}

.details-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.detail-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.detail-card h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 8px 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.page-link {
    color: #3498db;
    text-decoration: none;
}

.page-link:hover {
    text-decoration: underline;
}

.device-stats,
.browser-stats,
.referer-stats {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.device-item,
.browser-item,
.referer-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.device-item:last-child,
.browser-item:last-child,
.referer-item:last-child {
    border-bottom: none;
}

.device-bar {
    width: 100px;
    height: 6px;
    background: #f0f0f0;
    border-radius: 3px;
    overflow: hidden;
    margin-left: 10px;
}

.device-fill {
    height: 100%;
    background: #3498db;
    transition: width 0.3s ease;
}

.export-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.export-section h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.export-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 8px 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

.btn-outline-primary {
    color: #3498db;
    border-color: #3498db;
}

.btn-outline-primary:hover {
    background: #3498db;
    color: white;
}

.text-muted {
    color: #666;
    font-style: italic;
}
</style>
@endsection 