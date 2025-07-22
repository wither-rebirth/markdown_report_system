@extends('admin.layout')

@section('title', 'Report锁定管理')
@section('page-title', 'Report锁定管理')

@push('styles')
    @vite(['resources/css/admin/report-locks.css'])
@endpush

@section('content')
<div class="container">
    <div class="header-row">
        <div>
            <h1>🔒 Report锁定管理</h1>
            <p>管理报告的密码锁定设置</p>
        </div>
        <a href="{{ route('admin.report-locks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> 添加新锁定
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- 筛选器 -->
    <div class="filters-section">
        <form method="GET" action="{{ route('admin.report-locks.index') }}" class="filters-row">
            <div class="filter-group">
                <label class="filter-label">标签筛选</label>
                <select name="label" class="filter-select">
                    <option value="">所有标签</option>
                    @foreach($labels as $labelOption)
                        <option value="{{ $labelOption }}" {{ $label == $labelOption ? 'selected' : '' }}>
                            {{ ucfirst($labelOption) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">搜索</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="搜索标题、slug或描述..." class="filter-input">
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn btn-secondary">筛选</button>
                @if($label || $search)
                    <a href="{{ route('admin.report-locks.index') }}" class="btn btn-outline" style="margin-top: 0.5rem;">清除</a>
                @endif
            </div>
        </form>
    </div>

    @if($reportLocks->count() > 0)
        <!-- 批量操作 -->
        <div class="bulk-actions">
            <label>
                <input type="checkbox" id="select-all"> 全选
            </label>
            <select id="bulk-action" class="filter-select" style="width: auto;">
                <option value="">批量操作...</option>
                <option value="enable">启用锁定</option>
                <option value="disable">禁用锁定</option>
                <option value="delete">删除锁定</option>
            </select>
            <button type="button" id="apply-bulk-action" class="btn btn-secondary">应用</button>
        </div>

        <!-- 锁定列表 -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="select-all-header">
                        </th>
                        <th>报告</th>
                        <th>标签</th>
                        <th>密码</th>
                        <th>状态</th>
                        <th>锁定时间</th>
                        <th width="120">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportLocks as $lock)
                        <tr>
                            <td>
                                <input type="checkbox" class="select-item" value="{{ $lock->id }}">
                            </td>
                            <td>
                                <div>
                                    <strong style="color: #374151;">{{ $lock->title }}</strong>
                                    <br>
                                    <small style="color: #6b7280; font-family: monospace;">{{ $lock->slug }}</small>
                                    @if($lock->description)
                                        <br>
                                        <small style="color: #9ca3af;">{{ Str::limit($lock->description, 50) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="label-badge">{{ $lock->label }}</span>
                            </td>
                            <td>
                                <div class="password-preview" title="{{ $lock->password }}">
                                    {{ Str::limit($lock->password, 20) }}
                                </div>
                            </td>
                            <td>
                                <span class="status-badge {{ $lock->is_enabled ? 'status-enabled' : 'status-disabled' }}">
                                    {{ $lock->is_enabled ? '✅ 启用' : '❌ 禁用' }}
                                </span>
                            </td>
                            <td style="color: #6b7280; font-size: 0.875rem;">
                                {{ $lock->locked_at->format('Y-m-d H:i') }}
                            </td>
                            <td>
                                <div class="actions-group">
                                    <a href="{{ route('admin.report-locks.edit', $lock) }}" class="btn-small btn-edit" title="编辑">
                                        ✏️
                                    </a>
                                    <button type="button" class="btn-small btn-toggle" data-lock-id="{{ $lock->id }}" title="切换状态">
                                        {{ $lock->is_enabled ? '🔓' : '🔒' }}
                                    </button>
                                    <button type="button" class="btn-small btn-delete" data-lock-id="{{ $lock->id }}" data-lock-title="{{ $lock->title }}" title="删除">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        @if($reportLocks->hasPages())
            <div class="pagination-wrapper">
                <nav class="pagination-container">
                    <ul class="pagination">
                        {{-- Previous Page Link --}}
                        @if($reportLocks->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                                    </svg>
                                    Previous
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $reportLocks->appends(request()->query())->previousPageUrl() }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                                    </svg>
                                    Previous
                                </a>
                            </li>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach($reportLocks->appends(request()->query())->getUrlRange(max(1, $reportLocks->currentPage() - 2), min($reportLocks->lastPage(), $reportLocks->currentPage() + 2)) as $page => $url)
                            @if($page == $reportLocks->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if($reportLocks->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $reportLocks->appends(request()->query())->nextPageUrl() }}">
                                    Next
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                    </svg>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    Next
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                    </svg>
                                </span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
        
    @else
        <div class="no-results">
            <h3>😔 没有找到锁定记录</h3>
            <p>目前没有设置任何报告锁定，或搜索条件没有匹配的结果。</p>
            <a href="{{ route('admin.report-locks.create') }}" class="btn btn-primary">添加第一个锁定</a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/report-locks.js'])
@endpush 