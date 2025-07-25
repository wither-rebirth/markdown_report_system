@extends('admin.layout')

@section('title', 'Write New Article')
@section('page-title', 'Write New Article')

@push('styles')
@vite(['resources/css/admin/blog.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/blog.js'])
@endpush

@section('content')
<form action="{{ route('admin.blog.store') }}" method="POST" style="margin: 1.5rem;">
    @csrf
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Main Content -->
        <div>
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Article Content</h3>
                        <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
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
                            value="{{ old('title') }}"
                            placeholder="Enter article title"
                            required
                        >
                        @if($errors->has('title'))
                            <div class="form-error">{{ $errors->first('title') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="slug" class="form-label">Article Slug *</label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            class="form-input {{ $errors->has('slug') ? 'error' : '' }}"
                            value="{{ old('slug') }}"
                            placeholder="Article URL slug, leave blank for auto-generation"
                            pattern="[a-z0-9\-]+"
                        >
                        <div class="form-help">Only lowercase letters, numbers and hyphens allowed, used for article URL</div>
                        @if($errors->has('slug'))
                            <div class="form-error">{{ $errors->first('slug') }}</div>
                        @endif
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
                        >{{ old('excerpt') }}</textarea>
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
                        >{{ old('content') }}</textarea>
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
                            value="{{ old('author', Auth::user()->name) }}"
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
                                {{ old('published', true) ? 'checked' : '' }}
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
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($categories->count() == 0)
                            <div class="form-help">
                                <a href="{{ route('admin.categories.create') }}" target="_blank">No categories yet, create the first category</a>
                            </div>
                        @endif
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
                        <div style="max-height: 200px; overflow-y: auto;">
                            @foreach($tags as $tag)
                                <label style="display: block; margin-bottom: 0.5rem;">
                                    <input 
                                        type="checkbox" 
                                        name="tags[]" 
                                        value="{{ $tag->id }}"
                                        {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                        style="margin-right: 0.5rem;"
                                    >
                                    <span class="tag-name" data-color="{{ $tag->display_color }}">{{ $tag->name }}</span>
                                </label>
                            @endforeach
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
                            value="{{ old('image') }}"
                            placeholder="Image URL (optional)"
                        >
                        <div class="form-help">Full URL address of the article cover image</div>
                        @if($errors->has('image'))
                            <div class="form-error">{{ $errors->first('image') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> Save Article
                </button>
                <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</form>


@endsection 