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

// 设置标签颜色 - 优化版本
export function initTagColors() {
    // 使用更精确的选择器，只在需要的容器内查找
    const tagContainer = document.querySelector('.card-body');
    if (!tagContainer) return;
    
    const tagElements = tagContainer.querySelectorAll('.tag-name[data-color]');
    
    // 使用 requestAnimationFrame 来避免阻塞UI
    if (tagElements.length > 0) {
        requestAnimationFrame(() => {
            tagElements.forEach(function(span) {
                const color = span.dataset.color;
                if (color && color !== span.style.color) {
                    span.style.color = color;
                }
            });
        });
    }
}

// 博客表单初始化 - 添加防抖
export function initBlogForms() {
    // 使用防抖来避免频繁初始化
    debounce(() => {
        initSlugGeneration();
        initTagColors();
    }, 100)();
}

// 防抖函数
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
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

// 图片预览功能 - 使用事件委托和缓存
export function initImagePreview() {
    // 使用事件委托减少事件监听器数量
    document.addEventListener('blur', function(e) {
        if (e.target.type === 'url' && e.target.name === 'image') {
            // 添加延迟加载避免频繁处理
            setTimeout(() => showImagePreview(e.target), 100);
        }
    }, true);
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

// 确认删除功能 - 使用事件委托优化
export function initDeleteConfirmation() {
    // 使用事件委托，避免为每个按钮单独绑定事件
    document.addEventListener('click', function(e) {
        const deleteButton = e.target.closest('.btn-delete, [data-action="delete"]');
        if (deleteButton) {
            const title = deleteButton.dataset.title || '此项目';
            if (!confirm(`确定要删除 "${title}" 吗？此操作不可撤销。`)) {
                e.preventDefault();
                return false;
            }
        }
    });
}

// 批量选择功能 - 优化版本
export function initBatchSelection() {
    const selectAll = document.getElementById('select-all');
    const batchActions = document.querySelector('.batch-actions');
    
    if (!selectAll) return;
    
    // 使用更精确的查询，缓存结果
    const checkboxContainer = selectAll.closest('form') || document;
    const checkboxes = checkboxContainer.querySelectorAll('input[name="selected[]"]');
    
    if (checkboxes.length === 0) return;
    
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

// 标签搜索和选择优化
export function initTagsOptimization() {
    const tagSearch = document.getElementById('tag-search');
    const tagsContainer = document.getElementById('tags-container');
    const selectedCount = document.getElementById('selected-count');
    
    if (!tagsContainer) return;
    
    // 缓存所有标签元素
    const tagItems = Array.from(tagsContainer.querySelectorAll('.tag-item'));
    
    // 标签搜索功能（防抖）
    if (tagSearch) {
        let searchTimeout;
        tagSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterTags(this.value.toLowerCase(), tagItems);
            }, 150);
        });
    }
    
    // 优化标签选择计数
    if (selectedCount) {
        const checkboxes = tagsContainer.querySelectorAll('input[type="checkbox"]');
        
        // 使用事件委托优化性能
        tagsContainer.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox') {
                requestAnimationFrame(() => {
                    const checked = tagsContainer.querySelectorAll('input[type="checkbox"]:checked').length;
                    selectedCount.textContent = checked;
                });
            }
        });
    }
}

// 标签过滤函数
function filterTags(searchTerm, tagItems) {
    let visibleCount = 0;
    
    tagItems.forEach(item => {
        const tagName = item.dataset.tagName || '';
        const isVisible = tagName.includes(searchTerm);
        
        if (isVisible) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // 显示搜索结果统计
    const container = document.getElementById('tags-container');
    if (container) {
        let resultInfo = container.querySelector('.search-result-info');
        if (searchTerm) {
            if (!resultInfo) {
                resultInfo = document.createElement('div');
                resultInfo.className = 'search-result-info';
                resultInfo.style.cssText = 'font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem; padding: 0.5rem; background: #f9fafb; border-radius: 0.25rem;';
                container.insertBefore(resultInfo, container.firstChild);
            }
            resultInfo.textContent = `找到 ${visibleCount} 个匹配的标签`;
        } else if (resultInfo) {
            resultInfo.remove();
        }
    }
}

// 初始化所有blog功能
export function initBlogModule() {
    initBlogForms();
    initCharacterCount();
    initImagePreview();
    initDeleteConfirmation();
    initBatchSelection();
    initSearchEnhancement();
    initTagsOptimization();
}

// 页面加载完成后自动初始化
document.addEventListener('DOMContentLoaded', function() {
    initBlogModule();
}); 