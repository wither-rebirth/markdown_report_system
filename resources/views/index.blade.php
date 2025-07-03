@extends('layout')

@push('styles')
    @vite(['resources/css/index.css'])
@endpush

@section('content')
<div class="report-index">


    @if(count($reports) > 0)
    <!-- 报告列表 -->
    <div class="report-list">
        @foreach ($reports as $report)
        <a href="{{ route('reports.show', $report['slug']) }}" class="report-card-link">
            <div class="report-card" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <!-- 卡片左侧：图标和内容 -->
                <div class="card-main-content">
                    <div class="card-icon">
                        @switch(pathinfo($report['slug'], PATHINFO_EXTENSION))
                            @case('sql')
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M5,3H19A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3M5,5V19H19V5H5M7,7H17V9H7V7M7,11H17V13H7V11M7,15H17V17H7V15Z"/>
                                </svg>
                                @break
                            @case('xss')
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V15H11V17M11,13H13V7H11V13Z"/>
                                </svg>
                                @break
                            @default
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                        @endswitch
                    </div>
                    
                    <div class="card-content">
                        <h3 class="report-title">{{ $report['title'] }}</h3>
                        <p class="report-excerpt">{{ $report['excerpt'] ?? '点击查看完整内容...' }}</p>
                        
                        <div class="report-meta">
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M16.2,16.2L11,13V7H12.5V12.2L17,14.9L16.2,16.2Z"/>
                                </svg>
                                {{ date('Y-m-d H:i', $report['mtime']) }}
                            </span>
                            <span class="meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                                {{ number_format($report['size'] / 1024, 1) }} KB
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- 卡片右侧：箭头指示器 -->
                <div class="card-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                    </svg>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <!-- 空状态 -->
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
            </svg>
        </div>
        <h3>📭 暂无报告</h3>
        <p>开始上传你的第一个 Markdown 报告吧！</p>
        <div class="empty-actions">
            <a href="{{ route('reports.create') }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M17,13H13V17H11V13H7V11H11V7H13V11H17V13Z"/>
                </svg>
                上传报告
            </a>
        </div>
        <small class="text-muted">支持的文件格式: .md, .txt</small>
    </div>
    @endif
</div>



@push('scripts')
    @vite(['resources/js/index.js'])
@endpush
@endsection

