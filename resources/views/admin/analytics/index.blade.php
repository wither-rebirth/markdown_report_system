@extends('admin.layout')

@section('title', 'Analytics')
@section('page-title', 'Analytics')

@push('styles')
@vite(['resources/css/admin/analytics.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/analytics.js', 'resources/js/admin/confirm-dialog.js'])
@endpush

@section('content')
<div class="analytics-page">

    <!-- 控制面板 -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding: 1rem; background: var(--bg-primary); border: 1px solid var(--gray-200); border-radius: var(--radius-lg);">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Data Analytics</h2>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div class="period-selector">
                <label style="margin-right: 0.5rem; font-size: 0.875rem; color: var(--gray-600);">Time Period:</label>
                <select id="period-select" class="form-select" style="width: auto;" onchange="changePeriod(this.value)">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>Last 30 days</option>
                    <option value="90days" {{ $period === '90days' ? 'selected' : '' }}>Last 90 days</option>
                </select>
            </div>
            <a href="{{ route('admin.analytics.realtime') }}" class="btn btn-primary">
                <i class="fas fa-eye"></i> Real-time
            </a>
        </div>
    </div>

    <!-- 基础统计卡片 -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">👁️</div>
            <div class="stat-content">
                <h3>Page Views (PV)</h3>
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
                <h3>Unique Visitors (UV)</h3>
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
                <h3>Sessions</h3>
                <div class="stat-number">{{ number_format($basicStats['total_sessions']) }}</div>
                <div class="stat-sub">Avg {{ $basicStats['avg_pages_per_session'] }} pages/session</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>Bounce Rate</h3>
                <div class="stat-number">{{ $basicStats['bounce_rate'] }}%</div>
                <div class="stat-sub">New visitors {{ number_format($basicStats['new_visitors']) }}</div>
            </div>
        </div>
    </div>

    <!-- 趋势图表 -->
    <div class="chart-section">
        <div class="chart-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>Traffic Trend</h3>
                @if(config('app.debug'))
                    <button onclick="showDebugInfo()" class="btn btn-sm btn-outline-secondary">Debug Info</button>
                @endif
            </div>
            <canvas id="trendChart"></canvas>
            <script type="application/json" id="trend-data">{!! json_encode($trendData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        </div>
    </div>

    @if(config('app.debug'))
    <script>
    function showDebugInfo() {
        const element = document.getElementById('trend-data');
        const data = element ? element.textContent : 'Element not found';
        
        const debugInfo = `
Debug Information:
Data length: ${data.length}
First 100 characters: ${data.substring(0, 100)}
Data type: ${typeof data}
First character code: ${data.charCodeAt(0)}
Second character code: ${data.charCodeAt(1)}
Third character code: ${data.charCodeAt(2)}

Complete data:
${data}
        `;
        
        alert(debugInfo);
        console.log('Trend data debug:', {
            length: data.length,
            first100: data.substring(0, 100),
            fullData: data,
            charCodes: [data.charCodeAt(0), data.charCodeAt(1), data.charCodeAt(2)]
        });
    }
    </script>
    @endif

    <!-- 详细统计 -->
    <div class="details-section">
        <div class="detail-card">
            <h3>Top Pages</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>Page Views</th>
                            <th>Unique Visitors</th>
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
            <h3>Device Statistics</h3>
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
            <h3>Browser Statistics</h3>
            <div class="browser-stats">
                @foreach($browserStats as $browser)
                    <div class="browser-item">
                        <span class="browser-name">{{ $browser->browser }}</span>
                        <span class="browser-count">{{ number_format($browser->count) }}</span>
                    </div>
                @endforeach
            </div>
        </div>


    </div>

    <!-- 导出功能 -->
    <div class="export-section">
        <h3>Data Export</h3>
        <div class="export-buttons">
            <button onclick="exportData('visits')" class="btn btn-outline-primary">Export Visit Data</button>
            <button onclick="exportData('daily_stats')" class="btn btn-outline-primary">Export Statistics</button>
            <button onclick="exportData('pages')" class="btn btn-outline-primary">Export Page Data</button>
        </div>
    </div>
</div>




@endsection 