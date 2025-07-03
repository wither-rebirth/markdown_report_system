@extends('layout')

@push('styles')
    @vite(['resources/css/upload.css'])
@endpush

@section('content')
<div class="upload-container">
    <!-- 页面标题 -->
    <div class="upload-header">
        <h1 class="upload-title">📝 上传报告</h1>
    </div>

    @if ($errors->any())
    <div class="error-alert">
        <div class="error-icon">⚠️</div>
        <div class="error-content">
            <h3>上传失败</h3>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    @if (session('success'))
    <div class="success-alert">
        <div class="success-icon">✅</div>
        <div class="success-content">
            <h3>上传成功</h3>
            <p>{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <!-- 上传表单 -->
    <form id="upload-form" action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="upload-content">
            <!-- 文件上传区域 -->
            <div class="file-upload-area" id="file-upload-area">
                <div class="upload-placeholder">
                    <div class="upload-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                    </div>
                    <h3>拖拽或点击选择文件</h3>
                    <button type="button" class="btn-upload" onclick="document.getElementById('markdown_file').click()">
                        选择文件
                    </button>
                </div>
                
                <div class="file-info" id="file-info" style="display: none;">
                    <div class="file-icon">📄</div>
                    <div class="file-details">
                        <div class="file-name" id="file-name"></div>
                        <div class="file-size" id="file-size"></div>
                    </div>
                    <button type="button" class="remove-file" onclick="removeFile()">✕</button>
                </div>
                
                <input type="file" 
                       id="markdown_file" 
                       name="markdown_file" 
                       accept=".md,.txt" 
                       style="display: none;"
                       onchange="handleFileSelect(this.files[0])">
            </div>

            <!-- 文档信息 -->
            <div class="form-section">
                <div class="form-fields">
                    <div class="field-group">
                        <label for="title">标题</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               class="form-input" 
                               placeholder="可选"
                               value="{{ old('title') }}">
                    </div>
                    
                    <div class="field-group">
                        <label for="slug">链接</label>
                        <input type="text" 
                               id="slug" 
                               name="slug" 
                               class="form-input" 
                               placeholder="可选"
                               value="{{ old('slug') }}">
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="overwrite" value="1">
                        <span class="checkbox-custom"></span>
                        覆盖已存在的文件
                    </label>
                </div>
            </div>

            <!-- 操作按钮 -->
            <div class="action-buttons">
                <a href="{{ route('reports.index') }}" class="btn btn-back">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"/>
                    </svg>
                    返回
                </a>
                <button type="submit" class="btn btn-submit" id="submit-btn" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9,16.2L4.8,12L3.4,13.4L9,19L21,7L19.6,5.6L9,16.2Z"/>
                    </svg>
                    上传
                </button>
            </div>
        </div>
    </form>
</div>






@push('scripts')
    @vite(['resources/js/upload.js'])
@endpush
@endsection
