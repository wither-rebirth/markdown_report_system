@extends('layout', ['title' => $title])

@push('styles')
    @vite(['resources/css/report.css'])
@endpush

@section('content')
<div class="report-page">
    <!-- 报告头部信息 -->
    <div class="report-header" style="margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #e2e8f0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h1 style="margin: 0; color: var(--primary-color);">{{ $title }}</h1>
            <div class="no-print">
                <button onclick="window.print()" style="margin-right: 0.5rem;" title="打印报告">🖨️</button>
                <button onclick="toggleFullscreen()" title="全屏模式">🔍</button>
            </div>
        </div>
        
        <div class="report-meta">
            📅 更新时间: {{ date('Y年m月d日 H:i', $mtime) }} | 
            📄 大小: {{ number_format($size / 1024, 1) }} KB | 
            🔗 <a href="{{ route('reports.index') }}">返回列表</a>
        </div>
    </div>

    <!-- 目录 (如果内容较长) -->
    <div id="table-of-contents" class="no-print" style="margin-bottom: 2rem;"></div>

    <!-- 报告内容 -->
    <article class="report-content">
        {!! $html !!}
    </article>

    <!-- 返回顶部按钮 -->
    <div class="no-print" style="position: fixed; bottom: 2rem; right: 2rem;">
        <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
                style="background: var(--primary-color); color: white; border: none; padding: 0.75rem; border-radius: 50%; box-shadow: 0 2px 10px rgba(0,0,0,0.2);"
                title="返回顶部">
            ⬆️
        </button>
    </div>
</div>



@push('scripts')
    @vite(['resources/js/report.js'])
@endpush
@endsection

