@extends('admin.layout')

@section('title', '数据分析')
@section('page-title', '数据分析')

@push('styles')
@vite(['resources/css/admin/analytics.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/analytics.js'])
@endpush

@section('content')
<div class="analytics-page">

    <!-- 控制面板 -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding: 1rem; background: var(--bg-primary); border: 1px solid var(--gray-200); border-radius: var(--radius-lg);">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">数据统计</h2>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div class="period-selector">
                <label style="margin-right: 0.5rem; font-size: 0.875rem; color: var(--gray-600);">时间周期：</label>
                <select id="period-select" class="form-select" style="width: auto;" onchange="changePeriod(this.value)">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>今天</option>
                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>昨天</option>
                    <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>最近7天</option>
                    <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>最近30天</option>
                    <option value="90days" {{ $period === '90days' ? 'selected' : '' }}>最近90天</option>
                </select>
            </div>
            <a href="{{ route('admin.analytics.realtime') }}" class="btn btn-primary">
                <i class="fas fa-eye"></i> 实时数据
            </a>
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




@endsection 