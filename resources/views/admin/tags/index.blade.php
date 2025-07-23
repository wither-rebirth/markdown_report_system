@extends('admin.layout')

@section('title', 'Tag Management')
@section('page-title', 'Tag Management')

@push('styles')
@vite(['resources/css/admin/tags.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/tags.js', 'resources/js/admin/confirm-dialog.js'])
@endpush

@section('content')
<div class="card" style="margin: 1.5rem;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Tags List</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.tags.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Tag
                </a>
                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-select" style="width: 120px;">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-input" 
                        style="width: 200px;" 
                        placeholder="Search tag name..." 
                        value="{{ request('search') }}"
                    >
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
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
                            <th style="width: 35%;">Tag Name</th>
                            <th style="width: 25%;">Slug</th>
                            <th style="width: 20%;">Color</th>
                            <th style="width: 8%;">Status</th>
                            <th style="width: 7%;">Actions</th>
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
                                            Created: {{ $tag->created_at->format('Y-m-d') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.875rem;">{{ $tag->slug }}</code>
                                </td>
                                <td>
                                    <div class="tag-color-display">
                                        <div class="tag-color-swatch" data-bg-color="{{ $tag->display_color }}"></div>
                                        <code class="color-code">{{ $tag->color ?: 'Default' }}</code>
                                    </div>
                                </td>
                                <td>
                                    <label class="toggle-switch" title="Click to toggle status">
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
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.tags.destroy', $tag) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-confirm="Are you sure you want to delete tag '{{ $tag->name }}'?"
                                                    title="Delete">
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
                        <span id="selected-count">0 tags selected</span>
                    </div>
                    <div class="bulk-actions-buttons">
                        <button type="button" class="bulk-action-btn bulk-action-enable" id="bulk-enable">
                            <i class="fas fa-check"></i> Enable
                        </button>
                        <button type="button" class="bulk-action-btn bulk-action-disable" id="bulk-disable">
                            <i class="fas fa-ban"></i> Disable
                        </button>
                        <button type="button" class="bulk-action-btn bulk-action-delete" id="bulk-delete">
                            <i class="fas fa-trash"></i> Delete
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
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No Tags</h3>
                <p style="color: #9ca3af; margin-bottom: 1.5rem;">
                    @if(request('search'))
                        No tags found matching "{{ request('search') }}"
                    @else
                        No tags created yet, <a href="{{ route('admin.tags.create') }}">create your first tag now</a>
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>


@endsection 