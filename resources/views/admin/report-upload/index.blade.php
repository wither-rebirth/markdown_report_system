@extends('admin.layout')

@section('page-title', 'Report Upload')



@push('styles')
    @vite(['resources/css/admin/report-upload.css'])
@endpush

@section('content')
<div class="page-content">
    <!-- Statistics Cards -->
    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Reports</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['hackthebox'] }}</div>
            <div class="stat-label">HackTheBox</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['vulnhub'] }}</div>
            <div class="stat-label">VulnHub</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['tryhackme'] }}</div>
            <div class="stat-label">TryHackMe</div>
        </div>
    </div>

    <!-- Upload Forms -->
    <div class="content-grid">
        <!-- Single File Upload -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fab fa-markdown"></i> Single File Upload</h3>
                <p>Upload individual Markdown files (max 10MB)</p>
            </div>
            
            <form action="{{ route('admin.report-upload.markdown') }}" method="POST" enctype="multipart/form-data" class="upload-form">
                @csrf
                <div class="form-group">
                    <label>Select File</label>
                    <div class="file-drop-zone" data-input="markdown_file">
                        <input type="file" id="markdown_file" name="markdown_file" accept=".md,.markdown" required hidden>
                        <div class="drop-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Drop file here or click to browse</span>
                            <small>.md, .markdown (max 10MB)</small>
                        </div>
                        <div class="file-selected" style="display: none;">
                            <i class="fas fa-file-alt"></i>
                            <span class="file-name"></span>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Title (Optional)</label>
                        <input type="text" name="title" class="form-control" placeholder="Auto-extracted if empty">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="general">General</option>
                            <option value="hackthebox">HackTheBox</option>
                            <option value="vulnhub">VulnHub</option>
                            <option value="tryhackme">TryHackMe</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload File
                </button>
            </form>
        </div>

        <!-- Bulk ZIP Upload -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-file-archive"></i> Bulk ZIP Upload</h3>
                <p>Upload multiple reports in ZIP format (max 50MB)</p>
            </div>
            
            <form action="{{ route('admin.report-upload.zip') }}" method="POST" enctype="multipart/form-data" class="upload-form">
                @csrf
                <div class="form-group">
                    <label>Select ZIP File</label>
                    <div class="file-drop-zone" data-input="zip_file">
                        <input type="file" id="zip_file" name="zip_file" accept=".zip" required hidden>
                        <div class="drop-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Drop ZIP file here or click to browse</span>
                            <small>.zip (max 50MB)</small>
                        </div>
                        <div class="file-selected" style="display: none;">
                            <i class="fas fa-file-archive"></i>
                            <span class="file-name"></span>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="general">General</option>
                            <option value="hackthebox">HackTheBox</option>
                            <option value="vulnhub">VulnHub</option>
                            <option value="tryhackme">TryHackMe</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Structure</label>
                        <select name="extract_structure" class="form-control" required>
                            <option value="preserve">Preserve Structure</option>
                            <option value="flat">Flatten Files</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload & Extract
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// File drop zone handling
document.querySelectorAll('.file-drop-zone').forEach(zone => {
    const inputId = zone.dataset.input;
    const input = document.getElementById(inputId);
    const dropContent = zone.querySelector('.drop-content');
    const fileSelected = zone.querySelector('.file-selected');
    const fileName = zone.querySelector('.file-name');
    
    // Click to browse
    zone.addEventListener('click', () => input.click());
    
    // Drag & drop
    zone.addEventListener('dragover', (e) => {
        e.preventDefault();
        zone.classList.add('drag-over');
    });
    
    zone.addEventListener('dragleave', () => {
        zone.classList.remove('drag-over');
    });
    
    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            input.files = files;
            showSelectedFile(files[0]);
        }
    });
    
    // File input change
    input.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            showSelectedFile(e.target.files[0]);
        }
    });
    
    function showSelectedFile(file) {
        fileName.textContent = file.name;
        dropContent.style.display = 'none';
        fileSelected.style.display = 'flex';
    }
});

// Form submission
document.querySelectorAll('.upload-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        
        // Reset after timeout
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }, 30000);
    });
});
</script>
@endpush
@endsection