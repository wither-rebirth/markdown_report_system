// Upload 页面 JavaScript

let selectedFile = null;

// 初始化拖拽上传功能
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

// 处理文件选择
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
    
    // 显示文件信息
    document.querySelector('.upload-placeholder').style.display = 'none';
    document.getElementById('file-info').style.display = 'flex';
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = formatFileSize(file.size);
    document.getElementById('submit-btn').disabled = false;
    
    // 设置文件输入
    const fileInput = document.getElementById('markdown_file');
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;
    
    // 自动填充slug
    const slugInput = document.getElementById('slug');
    if (!slugInput.value) {
        const baseName = file.name.replace(/\.[^/.]+$/, "");
        slugInput.value = generateSlug(baseName);
    }
}

// 移除文件
function removeFile() {
    selectedFile = null;
    
    document.querySelector('.upload-placeholder').style.display = 'block';
    document.getElementById('file-info').style.display = 'none';
    document.getElementById('submit-btn').disabled = true;
    
    document.getElementById('markdown_file').value = '';
}

// 格式化文件大小
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// 生成slug
function generateSlug(text) {
    return text
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

// 文件验证
function validateFile(file) {
    const allowedTypes = ['text/markdown', 'text/plain'];
    const allowedExtensions = ['.md', '.txt'];
    const fileName = file.name.toLowerCase();
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    const errors = [];
    
    if (!allowedTypes.includes(file.type) && !allowedExtensions.some(ext => fileName.endsWith(ext))) {
        errors.push('只允许上传 .md 或 .txt 文件');
    }
    
    if (file.size > maxSize) {
        errors.push('文件大小不能超过 10MB');
    }
    
    if (file.size === 0) {
        errors.push('文件不能为空');
    }
    
    return errors;
}

// 增强的文件处理
function handleFileSelectEnhanced(file) {
    if (!file) return;
    
    const errors = validateFile(file);
    
    if (errors.length > 0) {
        alert(errors.join('\n'));
        return;
    }
    
    selectedFile = file;
    
    // 显示文件信息
    showFileInfo(file);
    
    // 启用提交按钮
    document.getElementById('submit-btn').disabled = false;
    
    // 设置文件输入
    setFileInput(file);
    
    // 自动填充字段
    autoFillFields(file);
}

// 显示文件信息
function showFileInfo(file) {
    document.querySelector('.upload-placeholder').style.display = 'none';
    const fileInfo = document.getElementById('file-info');
    fileInfo.style.display = 'flex';
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = formatFileSize(file.size);
    
    // 添加文件类型图标
    const fileIcon = document.querySelector('.file-icon');
    if (file.name.toLowerCase().endsWith('.md')) {
        fileIcon.textContent = '📝';
    } else if (file.name.toLowerCase().endsWith('.txt')) {
        fileIcon.textContent = '📄';
    } else {
        fileIcon.textContent = '📁';
    }
}

// 设置文件输入
function setFileInput(file) {
    const fileInput = document.getElementById('markdown_file');
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;
}

// 自动填充字段
function autoFillFields(file) {
    const slugInput = document.getElementById('slug');
    const titleInput = document.getElementById('title');
    
    if (!slugInput.value) {
        const baseName = file.name.replace(/\.[^/.]+$/, "");
        slugInput.value = generateSlug(baseName);
    }
    
    if (!titleInput.value) {
        const baseName = file.name.replace(/\.[^/.]+$/, "");
        titleInput.value = baseName.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
}

// 表单验证
function validateForm() {
    const fileInput = document.getElementById('markdown_file');
    const errors = [];
    
    if (!fileInput.files.length) {
        errors.push('请选择要上传的文件');
    }
    
    return errors;
}

// 提交表单前验证
function handleFormSubmit(event) {
    const errors = validateForm();
    
    if (errors.length > 0) {
        event.preventDefault();
        alert(errors.join('\n'));
        return false;
    }
    
    // 显示上传进度
    showUploadProgress();
    
    return true;
}

// 显示上传进度
function showUploadProgress() {
    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '上传中...';
    
    // 模拟进度条（实际项目中应该使用真实的上传进度）
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 10;
        submitBtn.innerHTML = `上传中... ${progress}%`;
        
        if (progress >= 100) {
            clearInterval(progressInterval);
            submitBtn.innerHTML = '上传完成';
        }
    }, 200);
}

// 键盘快捷键
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+Enter 提交表单
        if (e.ctrlKey && e.key === 'Enter') {
            const form = document.getElementById('upload-form');
            if (form && !document.getElementById('submit-btn').disabled) {
                form.submit();
            }
        }
        
        // Escape 取消选择文件
        if (e.key === 'Escape' && selectedFile) {
            removeFile();
        }
    });
}

// 粘贴文件功能
function initPasteUpload() {
    document.addEventListener('paste', function(e) {
        const items = e.clipboardData.items;
        
        for (let i = 0; i < items.length; i++) {
            if (items[i].kind === 'file') {
                const file = items[i].getAsFile();
                handleFileSelectEnhanced(file);
                break;
            }
        }
    });
}

// 页面初始化
document.addEventListener('DOMContentLoaded', function() {
    // 初始化拖拽上传
    initDragAndDrop();
    
    // 初始化键盘快捷键
    initKeyboardShortcuts();
    
    // 初始化粘贴上传
    initPasteUpload();
    
    // 标题输入自动生成slug
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.addEventListener('input', function(e) {
            const slugInput = document.getElementById('slug');
            if (!slugInput.value && e.target.value) {
                slugInput.value = generateSlug(e.target.value);
            }
        });
    }
    
    // 表单提交验证
    const form = document.getElementById('upload-form');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
    
    // 文件输入变化处理
    const fileInput = document.getElementById('markdown_file');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                handleFileSelectEnhanced(e.target.files[0]);
            }
        });
    }
    
    console.log('Upload 页面初始化完成');
});

// 导出函数供其他脚本使用
window.UploadPage = {
    handleFileSelect: handleFileSelectEnhanced,
    removeFile,
    generateSlug,
    formatFileSize,
    validateFile
}; 