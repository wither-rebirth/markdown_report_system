@extends('admin.layout')

@section('title', 'Edit Article')
@section('page-title', 'Edit Article')

@push('styles')
@vite(['resources/css/admin/blog.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/blog.js', 'resources/js/admin/confirm-dialog.js'])
@endpush

@section('content')
<form action="{{ route('admin.blog.update', $slug) }}" method="POST" style="margin: 1.5rem;">
    @csrf
    @method('PUT')
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Main Content -->
        <div>
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Article Content</h3>
                        <div class="d-flex gap-2">
                            <a href="{{ route('blog.show', $slug) }}" class="btn btn-outline" target="_blank">
                                <i class="fas fa-eye"></i> Preview
                            </a>
                            <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="title" class="form-label">Article Title *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-input {{ $errors->has('title') ? 'error' : '' }}"
                            value="{{ old('title', $post['title']) }}"
                            placeholder="Enter article title"
                            required
                        >
                        @if($errors->has('title'))
                            <div class="form-error">{{ $errors->first('title') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="slug" class="form-label">Article Slug</label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            class="form-input"
                            value="{{ $slug }}"
                            readonly
                            style="background-color: #f3f4f6; color: #6b7280;"
                        >
                        <div class="form-help">Article slug cannot be modified to maintain URL stability</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="excerpt" class="form-label">Article Excerpt</label>
                        <textarea 
                            id="excerpt" 
                            name="excerpt" 
                            class="form-textarea {{ $errors->has('excerpt') ? 'error' : '' }}"
                            placeholder="Enter article excerpt (optional)"
                            data-max-length="500"
                            style="height: 80px;"
                        >{{ old('excerpt', $post['excerpt']) }}</textarea>
                        <div class="form-help">Brief description of the article content, displayed in article lists</div>
                        @if($errors->has('excerpt'))
                            <div class="form-error">{{ $errors->first('excerpt') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="content" class="form-label">Article Content *</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            class="form-textarea {{ $errors->has('content') ? 'error' : '' }}"
                            placeholder="Enter article content (Markdown syntax supported)"
                            style="height: 400px;"
                            required
                        >{{ old('content', $post['content']) }}</textarea>
                        <div class="form-help">Supports Markdown syntax. For images, use relative paths: ![description](images/image.jpg)</div>
                        @if($errors->has('content'))
                            <div class="form-error">{{ $errors->first('content') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div>
            <!-- Publish Settings -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Publish Settings</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="author" class="form-label">Author *</label>
                        <input 
                            type="text" 
                            id="author" 
                            name="author" 
                            class="form-input {{ $errors->has('author') ? 'error' : '' }}"
                            value="{{ old('author', $post['author']) }}"
                            required
                        >
                        @if($errors->has('author'))
                            <div class="form-error">{{ $errors->first('author') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input 
                                type="checkbox" 
                                name="published" 
                                value="1" 
                                {{ old('published', $post['published'] ?? true) ? 'checked' : '' }}
                                style="margin-right: 0.5rem;"
                            >
                            Publish Immediately
                        </label>
                        <div class="form-help">Uncheck to save as draft</div>
                    </div>
                </div>
            </div>
            
            <!-- Category Settings -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Category</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ old('category_id', $post['category_id']) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Tags Settings -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Tags</h3>
                </div>
                <div class="card-body">
                    @if($tags->count() > 0)
                        <!-- Search Box -->
                        @if($tags->count() > 10)
                        <div style="margin-bottom: 1rem;">
                            <input 
                                type="text" 
                                id="tag-search" 
                                class="form-input" 
                                placeholder="Search tags..." 
                                style="font-size: 0.875rem; padding: 0.5rem;"
                                autocomplete="off"
                            >
                        </div>
                        @endif
                        
                        <!-- Tags List Container -->
                        <div id="tags-container" style="max-height: 200px; overflow-y: auto;">
                            @foreach($tags as $tag)
                                <label class="tag-item" data-tag-name="{{ strtolower($tag->name) }}" style="display: block; margin-bottom: 0.5rem;">
                                    <input 
                                        type="checkbox" 
                                        name="tags[]" 
                                        value="{{ $tag->id }}"
                                        {{ in_array($tag->id, old('tags', $postTags)) ? 'checked' : '' }}
                                        style="margin-right: 0.5rem;"
                                    >
                                    <span class="tag-name" data-color="{{ $tag->display_color }}">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        
                        <!-- Tags Statistics -->
                        <div style="margin-top: 1rem; font-size: 0.75rem; color: #6b7280;">
                            <span id="selected-count">{{ count($postTags) }}</span> / {{ $tags->count() }} tags selected
                        </div>
                    @else
                        <div class="form-help">
                            <a href="{{ route('admin.tags.create') }}" target="_blank">No tags yet, create the first tag</a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Featured Image -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Featured Image</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <input 
                            type="url" 
                            name="image" 
                            class="form-input {{ $errors->has('image') ? 'error' : '' }}"
                            value="{{ old('image', $post['image']) }}"
                            placeholder="Image URL (optional)"
                        >
                        <div class="form-help">Full URL address of the article cover image</div>
                        @if($errors->has('image'))
                            <div class="form-error">{{ $errors->first('image') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- File Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">File Information</h3>
                </div>
                <div class="card-body">
                    <div style="font-size: 0.875rem; color: #6b7280;">
                        <div style="margin-bottom: 0.5rem;">
                            <strong>File Path:</strong><br>
                            <code style="font-size: 0.75rem;">{{ $post['path'] }}</code>
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <strong>Last Modified:</strong><br>
                            {{ date('Y-m-d H:i:s', $post['mtime']) }}
                        </div>
                        <div>
                            <strong>File Size:</strong><br>
                            {{ number_format($post['size'] / 1024, 2) }} KB
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex gap-2 mb-2">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<!-- Delete Form - Separate form, not nested within update form -->
<div style="margin: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
    <div style="text-align: center;">
        <p style="margin-bottom: 1rem; color: #6b7280; font-size: 0.875rem;">
            Danger Zone: This action cannot be undone
        </p>
        <form action="{{ route('admin.blog.destroy', $slug) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="btn btn-danger" 
                    data-confirm="Are you sure you want to delete this article? This action cannot be undone!"
                    style="padding: 0.75rem 2rem; font-weight: 500;">
                <i class="fas fa-trash-alt"></i> Delete Article
            </button>
        </form>
    </div>
</div>




@endsection 