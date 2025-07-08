@extends('admin.layout')

@section('title', '实时数据')
@section('page-title', '实时数据')

@push('styles')
@vite(['resources/css/admin/analytics.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/analytics.js', 'resources/js/admin/confirm-dialog.js'])
@endpush

@section('content')
<div class="analytics-page realtime-page">

    <!-- 控制面板 -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding: 1rem; background: var(--bg-primary); border: 1px solid var(--gray-200); border-radius: var(--radius-lg);">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">实时数据监控</h2>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div class="realtime-indicator">
                <span style="display: inline-block; width: 8px; height: 8px; background: #22c55e; border-radius: 50%; margin-right: 0.5rem; animation: pulse 2s infinite;"></span>
                <span style="font-size: 0.875rem; color: var(--gray-600);">实时更新</span>
            </div>
            <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> 返回统计
            </a>
        </div>
    </div>

    <!-- 实时统计卡片 -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">🟢</div>
            <div class="stat-content">
                <h3>在线用户</h3>
                <div class="stat-number" id="online-users">{{ $onlineUsers }}</div>
                <div class="stat-sub">当前活跃</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
                <h3>24小时访问</h3>
                <div class="stat-number">{{ number_format($realtimeData['total_24h'] ?? 0) }}</div>
                <div class="stat-sub">最近24小时</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⏱️</div>
            <div class="stat-content">
                <h3>每分钟访问</h3>
                <div class="stat-number" id="current-ppm">{{ $realtimeData['current_ppm'] ?? 0 }}</div>
                <div class="stat-sub">过去1分钟</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🔄</div>
            <div class="stat-content">
                <h3>每小时访问</h3>
                <div class="stat-number">{{ number_format($realtimeData['current_pph'] ?? 0) }}</div>
                <div class="stat-sub">过去1小时</div>
            </div>
        </div>
    </div>

    <!-- 实时访问列表 -->
    <div class="realtime-visits">
        <div class="detail-card">
            <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1rem;">
                <h3>最新访问记录</h3>
                <button id="refresh-btn" class="btn btn-sm btn-outline-primary" onclick="refreshRealtimeData()">
                    <i class="fas fa-sync"></i> 刷新
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table" id="realtime-visits-table">
                    <thead>
                        <tr>
                            <th>时间</th>
                            <th>页面</th>
                            <th>IP地址</th>
                            <th>设备</th>
                            <th>浏览器</th>
                            <th>来源</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestVisits as $visit)
                            <tr class="visit-row" data-time="{{ $visit->created_at->timestamp }}">
                                <td>
                                    <span class="time-ago" data-time="{{ $visit->created_at->toISOString() }}">
                                        {{ $visit->created_at->diffForHumans() }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ $visit->url }}" target="_blank" class="page-link">
                                        {{ parse_url($visit->url, PHP_URL_PATH) ?: '/' }}
                                    </a>
                                </td>
                                <td>
                                    <code>{{ $visit->ip_address }}</code>
                                </td>
                                <td>
                                    <span class="device-badge">{{ $visit->device_type }}</span>
                                </td>
                                <td>
                                    <span class="browser-badge">{{ $visit->browser }}</span>
                                </td>
                                <td>
                                    <span class="referer-text">
                                        @if($visit->referer)
                                            {{ parse_url($visit->referer, PHP_URL_HOST) }}
                                        @else
                                            直接访问
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">暂无实时访问数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 24小时趋势图 -->
    <div class="chart-section">
        <div class="chart-container">
            <h3>24小时访问趋势</h3>
            <canvas id="realtimeTrendChart" height="300"></canvas>
            <script type="application/json" id="realtime-trend-data">{!! json_encode($realtimeData['hourly_trend'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.realtime-page .visit-row {
    transition: background-color 0.3s ease;
}

.realtime-page .visit-row.new {
    background-color: rgba(34, 197, 94, 0.1);
    animation: highlightNew 3s ease-out;
}

@keyframes highlightNew {
    0% { background-color: rgba(34, 197, 94, 0.3); }
    100% { background-color: transparent; }
}

.device-badge, .browser-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    background: var(--gray-100);
    color: var(--gray-700);
}

.time-ago {
    font-size: 0.875rem;
    color: var(--gray-600);
}

.page-link {
    color: var(--primary-600);
    text-decoration: none;
    max-width: 200px;
    display: inline-block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.page-link:hover {
    text-decoration: underline;
}

.referer-text {
    font-size: 0.875rem;
    color: var(--gray-600);
    max-width: 150px;
    display: inline-block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

#refresh-btn.loading {
    opacity: 0.6;
    pointer-events: none;
}

#refresh-btn.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
// 实时数据刷新功能
function refreshRealtimeData() {
    const btn = document.getElementById('refresh-btn');
    btn.classList.add('loading');
    
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // 解析返回的HTML，更新表格部分
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTableBody = doc.querySelector('#realtime-visits-table tbody');
        const currentTableBody = document.querySelector('#realtime-visits-table tbody');
        
        if (newTableBody && currentTableBody) {
            // 高亮新访问记录
            const newRows = newTableBody.querySelectorAll('.visit-row');
            const currentRows = currentTableBody.querySelectorAll('.visit-row');
            
            // 替换表格内容
            currentTableBody.innerHTML = newTableBody.innerHTML;
            
            // 为新记录添加高亮效果
            if (newRows.length > currentRows.length) {
                const addedCount = newRows.length - currentRows.length;
                const currentTableBodyNew = document.querySelector('#realtime-visits-table tbody');
                const firstRows = currentTableBodyNew.querySelectorAll('.visit-row');
                
                for (let i = 0; i < addedCount && i < firstRows.length; i++) {
                    firstRows[i].classList.add('new');
                }
            }
            
            // 更新在线用户数
            const newOnlineUsers = doc.querySelector('#online-users');
            const currentOnlineUsers = document.querySelector('#online-users');
            if (newOnlineUsers && currentOnlineUsers) {
                currentOnlineUsers.textContent = newOnlineUsers.textContent;
            }
            
            // 更新时间显示
            updateTimeAgo();
        }
    })
    .catch(error => {
        console.error('Failed to refresh realtime data:', error);
    })
    .finally(() => {
        btn.classList.remove('loading');
    });
}

// 更新时间显示
function updateTimeAgo() {
    document.querySelectorAll('.time-ago').forEach(element => {
        const time = element.getAttribute('data-time');
        if (time) {
            const date = new Date(time);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            let timeText;
            if (diff < 60) {
                timeText = diff + '秒前';
            } else if (diff < 3600) {
                timeText = Math.floor(diff / 60) + '分钟前';
            } else if (diff < 86400) {
                timeText = Math.floor(diff / 3600) + '小时前';
            } else {
                timeText = Math.floor(diff / 86400) + '天前';
            }
            
            element.textContent = timeText;
        }
    });
}

// 自动刷新功能
let autoRefreshInterval;

function startAutoRefresh() {
    // 每30秒自动刷新一次
    autoRefreshInterval = setInterval(refreshRealtimeData, 30000);
    
    // 每秒更新时间显示
    setInterval(updateTimeAgo, 1000);
}

// 页面加载时启动自动刷新
document.addEventListener('DOMContentLoaded', function() {
    updateTimeAgo();
    startAutoRefresh();
});

// 页面卸载时清除定时器
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>

@endsection 