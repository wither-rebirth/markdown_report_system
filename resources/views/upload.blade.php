@extends('layout')

@section('content')
<div class="upload-container">
    <!-- 页面标题 -->
    <div class="upload-header">
        <h1 class="upload-title">📝 上传Markdown报告</h1>
        <p class="upload-description">
            上传你的 Markdown 文件，系统将自动转换为美观的HTML报告
        </p>
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
        
        <!-- 文件上传区域 -->
        <div class="upload-section">
            <h2 class="section-title">📁 选择文件</h2>
            
            <div class="file-upload-area" id="file-upload-area">
                <div class="upload-placeholder">
                    <div class="upload-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                    </div>
                    <h3>拖拽文件到这里或点击选择</h3>
                    <p>支持 .md 和 .txt 文件，最大 10MB</p>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('markdown_file').click()">
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
            </div>
            
            <input type="file" 
                   id="markdown_file" 
                   name="markdown_file" 
                   accept=".md,.txt" 
                   style="display: none;"
                   onchange="handleFileSelect(this.files[0])">
        </div>

        <!-- 文档信息 -->
        <div class="document-info">
            <h2 class="section-title">📋 文档信息</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title">文档标题 (可选)</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           class="form-input" 
                           placeholder="如不填写，将从文档内容中提取"
                           value="{{ old('title') }}">
                    <small class="form-help">自定义文档标题，会覆盖文档中的# 标题</small>
                </div>
                
                <div class="form-group">
                    <label for="slug">URL别名 (可选)</label>
                    <input type="text" 
                           id="slug" 
                           name="slug" 
                           class="form-input" 
                           placeholder="如：my-awesome-report"
                           value="{{ old('slug') }}">
                    <small class="form-help">用于生成访问链接，如不填写将自动生成</small>
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="overwrite" value="1">
                    <span class="checkbox-custom"></span>
                    如果文件已存在，覆盖原文件
                </label>
            </div>
        </div>

        <!-- 操作按钮 -->
        <div class="upload-actions">
            <div class="action-buttons">
                <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"/>
                    </svg>
                    返回列表
                </a>
                <button type="submit" class="btn btn-primary upload-submit-btn" id="submit-btn" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9,16.2L4.8,12L3.4,13.4L9,19L21,7L19.6,5.6L9,16.2Z"/>
                    </svg>
                    开始上传报告
                </button>
            </div>
        </div>
    </form>
</div>
@push('styles')
<style>
.upload-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.upload-header {
    text-align: center;
    margin-bottom: 3rem;
}

.upload-title {
    font-size: 2.5rem;
    font-weight: 800;
    background: var(--gradient-cosmic);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
}

.upload-description {
    font-size: 1.1rem;
    color: var(--text-secondary);
    margin-bottom: 0;
}

.error-alert, .success-alert {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border-radius: var(--radius-lg);
    border: 1px solid;
}

.error-alert {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
    color: #dc2626;
}

.success-alert {
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.3);
    color: #059669;
}

.upload-section {
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.file-upload-area {
    border: 2px dashed var(--border-medium);
    border-radius: var(--radius-lg);
    padding: 2rem;
    text-align: center;
    transition: all var(--transition-normal);
    position: relative;
    overflow: hidden;
}

.file-upload-area.dragover {
    border-color: var(--primary-color);
    background: rgba(59, 130, 246, 0.05);
    transform: scale(1.02);
}

.file-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(59, 130, 246, 0.1);
    border-radius: var(--radius-md);
    text-align: left;
}

.file-icon {
    font-size: 2rem;
}

.file-details {
    flex: 1;
}

.file-name {
    font-weight: 600;
    color: var(--text-primary);
}

.file-size {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.remove-file {
    background: var(--error-color);
    border: none;
    color: white;
    cursor: pointer;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-sm);
    transition: all var(--transition-normal);
    font-weight: 600;
}

.remove-file:hover {
    background: #dc2626;
    transform: scale(1.05);
}

.document-info {
    background: rgba(255, 255, 255, 0.05);
    padding: 2rem;
    border-radius: var(--radius-lg);
    margin-bottom: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text-primary);
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-light);
    border-radius: var(--radius-md);
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    font-size: 1rem;
    transition: all var(--transition-normal);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    font-weight: 500;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkbox-custom {
    width: 20px;
    height: 20px;
    border: 2px solid var(--border-medium);
    border-radius: var(--radius-sm);
    position: relative;
    transition: all var(--transition-normal);
}

.checkbox-label input[type="checkbox"]:checked + .checkbox-custom {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.checkbox-label input[type="checkbox"]:checked + .checkbox-custom::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.upload-actions {
    margin-top: 2rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    align-items: center;
}

.action-buttons .btn {
    min-width: 160px;
    font-weight: 600;
    font-size: 1rem;
    padding: 1rem 2rem;
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.action-buttons .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.action-buttons .btn:hover::before {
    left: 100%;
}

.action-buttons .btn-secondary {
    background: white;
    color: var(--text-secondary);
    border: 2px solid var(--border-light);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

.action-buttons .btn-secondary:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border-color: var(--primary-color);
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.upload-submit-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.upload-submit-btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
}

.upload-submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.upload-submit-btn:disabled:hover {
    transform: none;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

/* 增强的文件上传区域样式 */
.file-upload-area {
    border: 2px dashed var(--border-medium);
    border-radius: var(--radius-lg);
    padding: 3rem 2rem;
    text-align: center;
    transition: all var(--transition-normal);
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.02) 0%, rgba(147, 197, 253, 0.05) 100%);
}

.file-upload-area::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.8s ease;
}

.file-upload-area:hover::before {
    left: 100%;
}

.file-upload-area.dragover {
    border-color: var(--primary-color);
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(147, 197, 253, 0.12) 100%);
    transform: scale(1.02);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.upload-placeholder h3 {
    margin: 1.5rem 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.25rem;
    font-weight: 600;
}

.upload-placeholder p {
    color: var(--text-muted);
    margin-bottom: 2rem;
    font-size: 1rem;
}

.upload-placeholder .btn {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
    color: white;
    border: none;
    padding: 0.875rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.upload-placeholder .btn:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

/* 文件信息显示优化 */
.file-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(52, 211, 153, 0.08) 100%);
    border: 2px solid rgba(16, 185, 129, 0.2);
    border-radius: var(--radius-lg);
    text-align: left;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1);
}

.file-icon {
    font-size: 2.5rem;
    color: var(--success-color);
}

.file-details {
    flex: 1;
}

.file-name {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}

.file-size {
    font-size: 0.875rem;
    color: var(--text-muted);
    font-weight: 500;
}

.remove-file {
    background: linear-gradient(135deg, var(--error-color) 0%, #dc2626 100%);
    border: none;
    color: white;
    cursor: pointer;
    padding: 0.75rem 1.25rem;
    border-radius: var(--radius-md);
    transition: all var(--transition-normal);
    font-weight: 600;
    font-size: 0.875rem;
}

.remove-file:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
}

/* 响应式设计改进 */
@media (max-width: 768px) {
    .upload-container {
        padding: 1rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 1rem;
    }
    
    .action-buttons .btn {
        width: 100%;
        min-width: auto;
    }
    
    .file-upload-area {
        padding: 2rem 1rem;
    }
    
    .upload-placeholder h3 {
        font-size: 1.1rem;
    }
    
    .file-info {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .file-details {
        order: -1;
    }
}

@media (max-width: 480px) {
    .upload-container {
        padding: 0.5rem;
    }
    
    .upload-title {
        font-size: 2rem;
    }
    
    .document-info,
    .upload-section {
        padding: 1.5rem;
    }
    
    .action-buttons .btn {
        padding: 0.875rem 1.5rem;
        font-size: 0.875rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
let selectedFile = null;

function initDragAndDrop() {
    const uploadArea = document.getElementById('file-upload-area');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });
    
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });
    
    uploadArea.addEventListener('drop', handleDrop, false);
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight(e) {
        uploadArea.classList.add('dragover');
    }
    
    function unhighlight(e) {
        uploadArea.classList.remove('dragover');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    }
}

function handleFileSelect(file) {
    if (!file) return;
    
    const allowedTypes = ['text/markdown', 'text/plain'];
    const allowedExtensions = ['.md', '.txt'];
    const fileName = file.name.toLowerCase();
    
    if (!allowedTypes.includes(file.type) && !allowedExtensions.some(ext => fileName.endsWith(ext))) {
        alert('只允许上传 .md 或 .txt 文件');
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
        alert('文件大小不能超过 10MB');
        return;
    }
    
    selectedFile = file;
    
    document.querySelector('.upload-placeholder').style.display = 'none';
    document.getElementById('file-info').style.display = 'flex';
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = formatFileSize(file.size);
    document.getElementById('submit-btn').disabled = false;
    
    const fileInput = document.getElementById('markdown_file');
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;
    
    const slugInput = document.getElementById('slug');
    if (!slugInput.value) {
        const baseName = file.name.replace(/\.[^/.]+$/, "");
        slugInput.value = generateSlug(baseName);
    }
}

function removeFile() {
    selectedFile = null;
    
    document.querySelector('.upload-placeholder').style.display = 'block';
    document.getElementById('file-info').style.display = 'none';
    document.getElementById('submit-btn').disabled = true;
    
    document.getElementById('markdown_file').value = '';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function generateSlug(text) {
    return text
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

document.getElementById('title').addEventListener('input', function(e) {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value && e.target.value) {
        slugInput.value = generateSlug(e.target.value);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    initDragAndDrop();
});
</script>
@endpush
@endsection
