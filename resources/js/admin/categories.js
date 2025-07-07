// Categories 模块 JavaScript 功能

// 消息显示功能
export function showMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    document.body.appendChild(messageDiv);
    
    // 显示消息
    setTimeout(() => {
        messageDiv.classList.add('show');
    }, 100);
    
    // 自动隐藏
    setTimeout(() => {
        messageDiv.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(messageDiv);
        }, 300);
    }, 3000);
}

// 状态切换功能 - 添加防抖和加载状态
export function initStatusToggle() {
    let categoryToggleTimeout = null;
    
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const isActive = this.checked;
            const toggleElement = this;
            
            // 防止重复点击
            if (toggleElement.disabled) return;
            
            // 清除之前的timeout
            if (categoryToggleTimeout) {
                clearTimeout(categoryToggleTimeout);
            }
            
            // 禁用开关，显示加载状态
            toggleElement.disabled = true;
            const slider = toggleElement.nextElementSibling;
            slider.style.opacity = '0.6';
            
            categoryToggleTimeout = setTimeout(() => {
                fetch(`/admin/categories/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ is_active: isActive })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('状态更新成功', 'success');
                    } else {
                        toggleElement.checked = !isActive;
                        showMessage('状态更新失败', 'error');
                    }
                })
                .catch(error => {
                    toggleElement.checked = !isActive;
                    showMessage('网络错误', 'error');
                })
                .finally(() => {
                    // 恢复开关状态
                    toggleElement.disabled = false;
                    slider.style.opacity = '1';
                    categoryToggleTimeout = null;
                });
            }, 300); // 300ms 防抖
        });
    });
}

// 排序移动功能 - 添加防抖和加载状态
export function initSortOrder() {
    let moveInProgress = false;
    
    document.querySelectorAll('.move-up, .move-down').forEach(function(btn) {
        btn.addEventListener('click', function() {
            // 防止重复点击
            if (moveInProgress) return;
            
            const id = this.dataset.id;
            const direction = this.classList.contains('move-up') ? 'up' : 'down';
            const buttonElement = this;
            
            // 设置加载状态
            moveInProgress = true;
            buttonElement.disabled = true;
            buttonElement.style.opacity = '0.6';
            
            // 禁用同行的移动按钮
            const row = buttonElement.closest('tr');
            const moveButtons = row.querySelectorAll('.move-up, .move-down');
            moveButtons.forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.6';
            });
            
            fetch(`/admin/categories/${id}/move`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ direction: direction })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('排序更新成功', 'success');
                    // 延迟刷新，让用户看到成功消息
                    setTimeout(() => {
                        location.reload();
                    }, 800);
                } else {
                    showMessage('排序更新失败', 'error');
                }
            })
            .catch(error => {
                showMessage('网络错误', 'error');
            })
            .finally(() => {
                // 恢复按钮状态
                moveInProgress = false;
                moveButtons.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                });
            });
        });
    });
}

// 删除确认功能
export function initDeleteConfirmation() {
    document.querySelectorAll('[data-confirm]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            const message = this.dataset.confirm;
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// Slug 自动生成功能
export function initSlugGeneration() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const slugPreview = document.getElementById('slug-preview');
    
    if (!nameInput || !slugInput) return;
    
    nameInput.addEventListener('input', function() {
        // 只在slug为空或者用户没有手动修改时自动生成
        if (slugInput.value === '' || slugInput.dataset.autoGenerated === 'true') {
            const slug = generateSlug(this.value);
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
            updateSlugPreview(slug);
        }
    });
    
    slugInput.addEventListener('input', function() {
        // 用户手动修改了slug
        if (this.value !== generateSlug(nameInput.value)) {
            this.dataset.autoGenerated = 'false';
        }
        updateSlugPreview(this.value);
    });
    
    function generateSlug(text) {
        return text
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '') // 移除特殊字符
            .replace(/\s+/g, '-') // 空格替换为横线
            .replace(/-+/g, '-') // 多个横线替换为单个
            .replace(/^-|-$/g, ''); // 移除开头和结尾的横线
    }
    
    function updateSlugPreview(slug) {
        if (!slugPreview) return;
        
        if (slug) {
            slugPreview.textContent = `/categories/${slug}`;
            slugPreview.className = 'slug-preview ' + (isValidSlug(slug) ? 'valid' : 'invalid');
        } else {
            slugPreview.textContent = '/categories/your-slug-here';
            slugPreview.className = 'slug-preview';
        }
    }
    
    function isValidSlug(slug) {
        return /^[a-z0-9-]+$/.test(slug) && slug.length >= 2 && slug.length <= 50;
    }
}

// 字符计数器功能
export function initCharCounter() {
    const textareas = document.querySelectorAll('[data-max-length]');
    
    textareas.forEach(function(textarea) {
        const maxLength = parseInt(textarea.dataset.maxLength);
        const counter = document.createElement('div');
        counter.className = 'char-counter';
        textarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const currentLength = textarea.value.length;
            const remaining = maxLength - currentLength;
            
            counter.textContent = `${currentLength}/${maxLength} 字符`;
            
            // 颜色警告
            if (remaining < 20) {
                counter.className = 'char-counter danger';
            } else if (remaining < 50) {
                counter.className = 'char-counter warning';
            } else {
                counter.className = 'char-counter';
            }
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter(); // 初始化
    });
}

// 颜色选择器功能
export function initColorPicker() {
    const colorInput = document.getElementById('color');
    const colorPreview = document.getElementById('color-preview');
    const presetColors = document.querySelectorAll('.preset-color');
    
    if (!colorInput) return;
    
    // 颜色输入变化
    colorInput.addEventListener('input', function() {
        updateColorPreview(this.value);
        updatePresetSelection(this.value);
    });
    
    // 预设颜色点击
    presetColors.forEach(function(preset) {
        preset.addEventListener('click', function() {
            const color = this.dataset.color;
            colorInput.value = color;
            updateColorPreview(color);
            updatePresetSelection(color);
        });
    });
    
    function updateColorPreview(color) {
        if (colorPreview) {
            colorPreview.style.backgroundColor = color;
        }
    }
    
    function updatePresetSelection(color) {
        presetColors.forEach(function(preset) {
            if (preset.dataset.color === color) {
                preset.classList.add('selected');
            } else {
                preset.classList.remove('selected');
            }
        });
    }
    
    // 初始化
    if (colorInput.value) {
        updateColorPreview(colorInput.value);
        updatePresetSelection(colorInput.value);
    }
}

// 表单验证功能
export function initFormValidation() {
    const form = document.querySelector('form');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        // 清除之前的错误状态
        form.querySelectorAll('.error').forEach(function(element) {
            element.classList.remove('error');
        });
        
        form.querySelectorAll('.form-error').forEach(function(element) {
            element.remove();
        });
        
        // 验证必填字段
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                showFieldError(field, '此字段为必填项');
                isValid = false;
            }
        });
        
        // 验证slug格式
        const slugInput = document.getElementById('slug');
        if (slugInput && slugInput.value) {
            if (!/^[a-z0-9-]+$/.test(slugInput.value)) {
                showFieldError(slugInput, 'Slug只能包含小写字母、数字和横线');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            showMessage('请检查表单中的错误', 'error');
        }
    });
    
    function showFieldError(field, message) {
        field.classList.add('error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }
}

// 表格排序功能
export function initTableSorting() {
    const table = document.querySelector('.data-table table');
    if (!table) return;
    
    const headers = table.querySelectorAll('th');
    
    headers.forEach(function(header, index) {
        // 跳过checkbox列和操作列
        if (header.querySelector('input[type="checkbox"]') || 
            header.textContent.includes('操作')) {
            return;
        }
        
        header.style.cursor = 'pointer';
        header.style.userSelect = 'none';
        
        header.addEventListener('click', function() {
            sortTableByColumn(table, index);
        });
        
        // 添加排序指示器
        header.addEventListener('mouseenter', function() {
            if (!this.querySelector('.sort-indicator')) {
                const indicator = document.createElement('span');
                indicator.className = 'sort-indicator';
                indicator.textContent = ' ↕';
                indicator.style.opacity = '0.5';
                this.appendChild(indicator);
            }
        });
    });
}

function sortTableByColumn(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const isNumeric = rows.some(row => {
        const cell = row.cells[columnIndex];
        const text = cell?.textContent.trim().replace(/[,\s]/g, '') || '';
        return !isNaN(parseFloat(text)) && isFinite(text);
    });
    
    rows.sort((a, b) => {
        const aVal = a.cells[columnIndex]?.textContent.trim() || '';
        const bVal = b.cells[columnIndex]?.textContent.trim() || '';
        
        if (isNumeric) {
            return parseFloat(bVal.replace(/[,\s]/g, '')) - parseFloat(aVal.replace(/[,\s]/g, ''));
        } else {
            return bVal.localeCompare(aVal, 'zh-CN');
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// 初始化所有categories模块功能
export function initCategoriesModule() {
    initStatusToggle();
    initSortOrder();
    initDeleteConfirmation();
    initSlugGeneration();
    initCharCounter();
    initColorPicker();
    initFormValidation();
    initTableSorting();
}

// 将函数暴露到全局作用域（用于兼容性）
if (typeof window !== 'undefined') {
    window.showMessage = showMessage;
}

// 页面加载完成后自动初始化
document.addEventListener('DOMContentLoaded', function() {
    initCategoriesModule();
}); 