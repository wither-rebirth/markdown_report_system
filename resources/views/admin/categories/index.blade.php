@extends('admin.layout')

@section('title', '分类管理')
@section('page-title', '分类管理')

@push('styles')
@vite(['resources/css/admin/categories.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/categories.js', 'resources/js/admin/confirm-dialog.js'])
@endpush

@section('content')
<div class="card" style="margin: 1.5rem;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">分类列表</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 新建分类
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
                        placeholder="搜索分类名称..." 
                        value="{{ request('search') }}"
                    >
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> 搜索
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> 清除
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        @if($categories->count() > 0)
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 30%;">分类名称</th>
                            <th style="width: 20%;">别名</th>
                            <th style="width: 25%;">描述</th>
                            <th style="width: 10%;">排序</th>
                            <th style="width: 8%;">状态</th>
                            <th style="width: 7%;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="categories-tbody">
                        @foreach($categories as $category)
                            <tr data-id="{{ $category->id }}">
                                <td>
                                    <div>
                                        <strong>{{ $category->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            创建时间：{{ $category->created_at->format('Y-m-d') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.875rem;">{{ $category->slug }}</code>
                                </td>
                                <td>
                                    @if($category->description)
                                        <span title="{{ $category->description }}">
                                            {{ Str::limit($category->description, 50) }}
                                        </span>
                                    @else
                                        <span class="text-muted">未填写</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline move-up" 
                                                data-id="{{ $category->id }}"
                                                title="上移">
                                            <i class="fas fa-chevron-up"></i>
                                        </button>
                                        <span style="font-weight: 600; min-width: 30px; text-align: center;">
                                            {{ $category->sort_order }}
                                        </span>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline move-down" 
                                                data-id="{{ $category->id }}"
                                                title="下移">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <label class="toggle-switch" title="点击切换状态">
                                        <input type="checkbox" 
                                               data-id="{{ $category->id }}"
                                               {{ $category->is_active ? 'checked' : '' }}
                                               class="status-toggle">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.categories.edit', $category) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="编辑">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-confirm="确定要删除分类「{{ $category->name }}」吗？"
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
            
            @if($categories->hasPages())
                <div style="padding: 1rem;">
                    {{ $categories->links() }}
                </div>
            @endif
        @else
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <i class="fas fa-folder-open" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">暂无分类</h3>
                <p style="color: #9ca3af; margin-bottom: 1.5rem;">
                    @if(request('search'))
                        没有找到匹配"{{ request('search') }}"的分类
                    @else
                        还没有创建任何分类，<a href="{{ route('admin.categories.create') }}">立即创建第一个分类</a>
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>


@endsection 