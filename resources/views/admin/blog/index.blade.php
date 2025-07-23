@extends('admin.layout')

@section('title', 'Blog Management')
@section('page-title', 'Blog Management')

@push('styles')
@vite(['resources/css/admin/blog.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/blog.js', 'resources/js/admin/confirm-dialog.js'])
@endpush

@section('content')
<div class="card" style="margin: 1.5rem;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Posts List</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.blog.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Post
                </a>
                <form method="GET" class="d-flex gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-input" 
                        style="width: 250px;" 
                        placeholder="Search title, author..." 
                        value="{{ $search }}"
                    >
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    @if($search)
                        <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        @if(count($posts) > 0)
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40%;">Title</th>
                            <th style="width: 15%;">Author</th>
                            <th style="width: 15%;">Category</th>
                            <th style="width: 15%;">Updated</th>
                            <th style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $post)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $post['title'] }}</strong>
                                        @if(!empty($post['excerpt']))
                                            <br>
                                            <small class="text-muted">{{ Str::limit($post['excerpt'], 80) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $post['author'] }}</td>
                                <td>
                                    @if($post['category'])
                                        <span class="status-badge active">{{ $post['category'] }}</span>
                                    @else
                                        <span class="text-muted">Uncategorized</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ date('Y-m-d H:i', $post['mtime']) }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('blog.show', $post['slug']) }}" 
                                           class="btn btn-sm btn-secondary" 
                                           target="_blank" 
                                           title="Preview">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.blog.edit', $post['slug']) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.blog.destroy', $post['slug']) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-confirm="Are you sure you want to delete this post?"
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
        @else
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <i class="fas fa-file-alt" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No Posts</h3>
                <p style="color: #9ca3af; margin-bottom: 1.5rem;">
                    @if($search)
                        No posts found matching "{{ $search }}"
                    @else
                        No posts published yet, <a href="{{ route('admin.blog.create') }}">create your first post now</a>
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>


@endsection 