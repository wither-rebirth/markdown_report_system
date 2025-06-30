@extends('layout')

@section('content')
<div class="report-index">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>📚 报告列表</h1>
        <small style="color: var(--secondary-color);">共 {{ count($reports) }} 个报告</small>
    </div>

    @if(count($reports) > 3)
    <!-- 搜索框 -->
    <div style="margin-bottom: 2rem;">
        <input type="text" 
               id="report-search" 
               placeholder="🔍 搜索报告标题或内容..." 
               style="width: 100%;">
    </div>
    @endif

    @if(count($reports) > 0)
    <!-- 报告列表 -->
    <div class="report-list">
        @foreach ($reports as $report)
        <div class="report-card">
            <h3 class="report-title">
                <a href="{{ url($report['slug'].'.html') }}">{{ $report['title'] }}</a>
            </h3>
            <div class="report-meta">
                📅 更新时间: {{ date('Y年m月d日 H:i', $report['mtime']) }} | 
                📄 大小: {{ number_format($report['size'] / 1024, 1) }} KB |
                🔗 <a href="{{ url($report['slug'].'.html') }}">查看详情</a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <!-- 空状态 -->
    <div style="text-align: center; padding: 3rem; color: var(--secondary-color);">
        <h3>📭 暂无报告</h3>
        <p>将 Markdown 文件放入 <code>storage/reports/</code> 目录即可开始使用</p>
        <small>支持的文件格式: .md</small>
    </div>
    @endif
</div>

@push('scripts')
<script>
// 如果URL中有hash，平滑滚动到对应元素
if (window.location.hash) {
    setTimeout(() => {
        const target = document.querySelector(window.location.hash);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    }, 100);
}
</script>
@endpush
@endsection

