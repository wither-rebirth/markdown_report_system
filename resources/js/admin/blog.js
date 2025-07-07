// Blog 模块 JavaScript 功能

// 自动生成 slug 功能
export function initSlugGeneration() {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugInput.dataset.userModified) {
                slugInput.value = generateSlug(this.value);
            }
        });

        slugInput.addEventListener('input', function() {
            this.dataset.userModified = 'true';
        });
    }
}

// 生成 slug 函数
function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

// 设置标签颜色
export function initTagColors() {
    document.querySelectorAll('.tag-name').forEach(function(span) {
        const color = span.dataset.color;
        if (color) {
            span.style.color = color;
        }
    });
}

// 博客表单初始化
export function initBlogForms() {
    initSlugGeneration();
    initTagColors();
}

// 文本区域字符计数
export function initCharacterCount() {
    const textareas = document.querySelectorAll('textarea[data-max-length]');
    
    textareas.forEach(function(textarea) {
        const maxLength = parseInt(textarea.dataset.maxLength);
        const counter = document.createElement('div');
        counter.className = 'character-counter';
        counter.style.cssText = 'font-size: 0.75rem; color: #6b7280; text-align: right; margin-top: 0.25rem;';
        
        textarea.parentNode.insertBefore(counter, textarea.nextSibling);
        
        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${textarea.value.length}/${maxLength} 字符`;
            
            if (remaining < 50) {
                counter.style.color = '#ef4444';
            } else if (remaining < 100) {
                counter.style.color = '#f59e0b';
            } else {
                counter.style.color = '#6b7280';
            }
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
}

// 图片预览功能
export function initImagePreview() {
    const imageInputs = document.querySelectorAll('input[type="url"][name="image"]');
    
    imageInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            showImagePreview(this);
        });
    });
}

function showImagePreview(input) {
    const url = input.value.trim();
    let preview = input.parentNode.querySelector('.image-preview');
    
    if (!url) {
        if (preview) {
            preview.remove();
        }
        return;
    }
    
    if (!preview) {
        preview = document.createElement('div');
        preview.className = 'image-preview';
        preview.style.cssText = 'margin-top: 0.5rem; text-align: center;';
        input.parentNode.appendChild(preview);
    }
    
    preview.innerHTML = `
        <img src="${url}" 
             style="max-width: 100%; max-height: 150px; border-radius: 0.5rem; object-fit: cover;"
             onerror="this.parentNode.innerHTML='<span style=color:#ef4444;font-size:0.75rem;>图片加载失败</span>'"
             onload="this.style.display='block'">
    `;
}

// 确认删除功能
export function initDeleteConfirmation() {
    const deleteButtons = document.querySelectorAll('.btn-delete, [data-action="delete"]');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const title = this.dataset.title || '此项目';
            if (!confirm(`确定要删除 "${title}" 吗？此操作不可撤销。`)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// 批量选择功能
export function initBatchSelection() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    const batchActions = document.querySelector('.batch-actions');
    
    if (!selectAll || checkboxes.length === 0) return;
    
    // 全选/取消全选
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = selectAll.checked;
        });
        updateBatchActions();
    });
    
    // 单个选择
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateBatchActions();
        });
    });
    
    function updateSelectAllState() {
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        selectAll.checked = checkedCount === checkboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    }
    
    function updateBatchActions() {
        const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        if (batchActions) {
            batchActions.style.display = selectedCount > 0 ? 'block' : 'none';
            const countSpan = batchActions.querySelector('.selected-count');
            if (countSpan) {
                countSpan.textContent = selectedCount;
            }
        }
    }
}

// 搜索功能增强
export function initSearchEnhancement() {
    const searchInput = document.querySelector('input[name="search"]');
    const searchForm = searchInput?.closest('form');
    
    if (!searchInput || !searchForm) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (this.value.length >= 2 || this.value.length === 0) {
                searchForm.submit();
            }
        }, 500);
    });
}

// 初始化所有blog功能
export function initBlogModule() {
    initBlogForms();
    initCharacterCount();
    initImagePreview();
    initDeleteConfirmation();
    initBatchSelection();
    initSearchEnhancement();
}

// 页面加载完成后自动初始化
document.addEventListener('DOMContentLoaded', function() {
    initBlogModule();
}); 