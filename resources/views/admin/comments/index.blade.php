@extends('admin.layout')

@section('title', 'Comment Management')
@section('page-title', 'Comment Management')

@push('styles')
@vite(['resources/css/admin/comments.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/comments.js', 'resources/js/admin/confirm-dialog.js'])
@endpush

@section('content')
<div class="card" style="margin: 1.5rem;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Comments List</h3>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" id="batch-approve" style="display: none;">
                    <i class="fas fa-check"></i> Bulk Approve
                </button>
                <button type="button" class="btn btn-warning" id="batch-spam" style="display: none;">
                    <i class="fas fa-ban"></i> Mark as Spam
                </button>
                <button type="button" class="btn btn-danger" id="batch-delete" style="display: none;">
                    <i class="fas fa-trash"></i> Bulk Delete
                </button>
                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-select" style="width: 120px;">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="spam" {{ request('status') == 'spam' ? 'selected' : '' }}>Spam</option>
                    </select>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-input" 
                        style="width: 200px;" 
                        placeholder="Search content, author..." 
                        value="{{ request('search') }}"
                    >
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        @if($comments->count() > 0)
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">
                                <input type="checkbox" id="select-all" style="margin: 0;">
                            </th>
                            <th style="width: 20%;">Commenter</th>
                            <th style="width: 30%;">Comment Content</th>
                            <th style="width: 15%;">Post</th>
                            <th style="width: 10%;">Submitted</th>
                            <th style="width: 8%;">Status</th>
                            <th style="width: 12%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comments as $comment)
                            <tr class="comment-row {{ $comment->status === 'pending' ? 'comment-pending' : '' }}">
                                <td>
                                    <input type="checkbox" 
                                           name="selected_comments[]" 
                                           value="{{ $comment->id }}" 
                                           class="comment-checkbox"
                                           style="margin: 0;">
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $comment->author_name }}</strong>
                                        @if($comment->author_email)
                                            <br>
                                            <small class="text-muted">{{ $comment->author_email }}</small>
                                        @endif
                                        @if($comment->author_website)
                                            <br>
                                            <small>
                                                <a href="{{ $comment->author_website }}" 
                                                   target="_blank" 
                                                   style="color: #667eea; text-decoration: none;">
                                                    <i class="fas fa-external-link-alt"></i> Website
                                                </a>
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="max-height: 80px; overflow-y: auto;">
                                        {{ Str::limit($comment->content, 120) }}
                                    </div>
                                    @if($comment->parent_id)
                                        <small class="text-muted">
                                            <i class="fas fa-reply"></i> Reply to comment
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('blog.show', $comment->blog_slug) }}" 
                                       target="_blank" 
                                       style="color: #667eea; text-decoration: none;">
                                        {{ Str::limit($comment->blog_slug, 20) }}
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>
                                <td>
                                    <small>{{ $comment->created_at->format('m-d H:i') }}</small>
                                </td>
                                <td>
                                    @if($comment->status === 'pending')
                                        <span class="status-badge warning">Pending</span>
                                    @elseif($comment->status === 'approved')
                                        <span class="status-badge active">Approved</span>
                                    @elseif($comment->status === 'spam')
                                        <span class="status-badge danger">Spam</span>
                                    @else
                                        <span class="status-badge inactive">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($comment->status === 'pending')
                                            <button type="button" 
                                                    class="btn btn-sm btn-success approve-btn" 
                                                    data-id="{{ $comment->id }}"
                                                    title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('admin.comments.edit', $comment) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if($comment->status !== 'spam')
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning spam-btn" 
                                                    data-id="{{ $comment->id }}"
                                                    title="Mark as Spam">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                        
                                        <form action="{{ route('admin.comments.destroy', $comment) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-confirm="Are you sure you want to delete this comment?"
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
            
            @if($comments->hasPages())
                <div style="padding: 1rem;">
                    {{ $comments->links() }}
                </div>
            @endif
        @else
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <i class="fas fa-comments" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No Comments</h3>
                <p style="color: #9ca3af; margin-bottom: 1.5rem;">
                    @if(request('search'))
                        No comments found matching "{{ request('search') }}"
                    @else
                        No comments received yet
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>


@endsection 