@extends('admin.layout')

@section('title', '标签管理')
@section('page-title', '标签管理')

@push('styles')
@vite(['resources/css/admin/tags.css'])
@endpush

@section('content')
<div class="card" style="margin: 1.5rem;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">标签列表</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.tags.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 新建标签
                </a>
                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-select" style="width: 120px;">
                        <option value="">全部状态</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>启用</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>禁用</option>
                    </select>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-input" 
                        style="width: 200px;" 
                        placeholder="搜索标签名称..." 
                        value="{{ request('search') }}"
                    >
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> 搜索
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> 清除
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        @if($tags->count() > 0)
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">
                                <input type="checkbox" id="select-all" style="margin: 0;">
                            </th>
                            <th style="width: 35%;">标签名称</th>
                            <th style="width: 25%;">别名</th>
                            <th style="width: 20%;">颜色</th>
                            <th style="width: 8%;">状态</th>
                            <th style="width: 7%;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tags as $tag)
                            <tr>
                                <td>
                                    <input type="checkbox" 
                                           name="selected_tags[]" 
                                           value="{{ $tag->id }}" 
                                           class="tag-checkbox"
                                           style="margin: 0;">
                                </td>
                                <td>
                                    <div class="tag-info">
                                        <div class="tag-name-wrapper">
                                            <span class="tag-preview" data-color="{{ $tag->display_color }}">
                                                {{ $tag->name }}
                                            </span>
                                            <strong class="tag-name-display">{{ $tag->name }}</strong>
                                        </div>
                                        <small class="tag-meta">
                                            创建：{{ $tag->created_at->format('Y-m-d') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.875rem;">{{ $tag->slug }}</code>
                                </td>
                                <td>
                                    <div class="tag-color-display">
                                        <div class="tag-color-swatch" data-bg-color="{{ $tag->display_color }}"></div>
                                        <code class="color-code">{{ $tag->color ?: '默认' }}</code>
                                    </div>
                                </td>
                                <td>
                                    <label class="toggle-switch" title="点击切换状态">
                                        <input type="checkbox" 
                                               data-id="{{ $tag->id }}"
                                               {{ $tag->is_active ? 'checked' : '' }}
                                               class="status-toggle">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.tags.edit', $tag) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="编辑">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.tags.destroy', $tag) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-confirm="确定要删除标签「{{ $tag->name }}」吗？"
                                                    title="删除">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- 批量操作 -->
            <div class="bulk-actions-bar" id="bulk-actions">
                <div class="bulk-actions-content">
                    <div class="bulk-actions-info">
                        <i class="fas fa-check-square"></i>
                        <span id="selected-count">已选择 0 个标签</span>
                    </div>
                    <div class="bulk-actions-buttons">
                        <button type="button" class="bulk-action-btn bulk-action-enable" id="bulk-enable">
                            <i class="fas fa-check"></i> 启用
                        </button>
                        <button type="button" class="bulk-action-btn bulk-action-disable" id="bulk-disable">
                            <i class="fas fa-ban"></i> 禁用
                        </button>
                        <button type="button" class="bulk-action-btn bulk-action-delete" id="bulk-delete">
                            <i class="fas fa-trash"></i> 删除
                        </button>
                    </div>
                </div>
            </div>
            
            @if($tags->hasPages())
                <div style="padding: 1rem;">
                    {{ $tags->links() }}
                </div>
            @endif
        @else
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <i class="fas fa-tags" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">暂无标签</h3>
                <p style="color: #9ca3af; margin-bottom: 1.5rem;">
                    @if(request('search'))
                        没有找到匹配"{{ request('search') }}"的标签
                    @else
                        还没有创建任何标签，<a href="{{ route('admin.tags.create') }}">立即创建第一个标签</a>
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>


@endsection 