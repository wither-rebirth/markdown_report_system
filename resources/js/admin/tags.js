// Tags 模块 JavaScript 功能

// 导入categories的基础功能
import { showMessage, initDeleteConfirmation, initSlugGeneration, initCharCounter, initFormValidation } from './categories.js';

// 设置标签颜色显示
export function initTagColors() {
    // 设置标签预览颜色
    const tagPreviews = document.querySelectorAll('.tag-preview');
    
    tagPreviews.forEach(function(element) {
        const color = element.dataset.color;
        
        if (color && color !== 'undefined' && color !== '') {
            element.style.backgroundColor = color;
            
            // 根据颜色亮度调整文字颜色
            if (isLightColor(color)) {
                element.classList.add('light-bg');
                element.classList.remove('dark-bg');
                element.style.color = '#000000';
            } else {
                element.classList.add('dark-bg');
                element.classList.remove('light-bg');
                element.style.color = '#ffffff';
            }
        } else {
            // 如果没有颜色，使用默认颜色
            element.style.backgroundColor = '#6366f1';
            element.classList.add('dark-bg');
            element.classList.remove('light-bg');
            element.style.color = '#ffffff';
        }
    });

    // 设置颜色色块
    const colorSwatches = document.querySelectorAll('.tag-color-swatch');
    
    colorSwatches.forEach(function(element) {
        const bgColor = element.dataset.bgColor;
        
        if (bgColor && bgColor !== 'undefined' && bgColor !== '') {
            element.style.backgroundColor = bgColor;
        } else {
            // 如果没有颜色，使用默认颜色
            element.style.backgroundColor = '#6366f1';
        }
    });
}

// 判断颜色是否为浅色
function isLightColor(color) {
    // 转换颜色为RGB
    let r, g, b;
    
    if (color.match(/^rgb/)) {
        color = color.match(/rgba?\(([^)]+)\)/)[1];
        color = color.split(/ *, */).map(Number);
        r = color[0];
        g = color[1];
        b = color[2];
    } else if (color[0] === '#') {
        color = color.slice(1);
        if (color.length === 3) {
            color = color.split('').map(c => c + c).join('');
        }
        r = parseInt(color.slice(0, 2), 16);
        g = parseInt(color.slice(2, 4), 16);
        b = parseInt(color.slice(4, 6), 16);
    } else {
        return false;
    }
    
    // 计算亮度
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness > 155;
}

// 全选功能
export function initSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    if (!selectAllCheckbox) return;
    
    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.tag-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });
}

// 单个选择框
export function initIndividualSelect() {
    document.querySelectorAll('.tag-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
}

// 更新批量操作显示
export function updateBulkActions() {
    const selected = document.querySelectorAll('.tag-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        setTimeout(() => bulkActions.classList.add('show'), 10);
        selectedCount.textContent = `已选择 ${selected.length} 个标签`;
    } else {
        bulkActions.classList.remove('show');
        setTimeout(() => {
            if (!bulkActions.classList.contains('show')) {
                bulkActions.style.display = 'none';
            }
        }, 300);
    }
}

// 批量启用
export function initBulkEnable() {
    const bulkEnableBtn = document.getElementById('bulk-enable');
    if (!bulkEnableBtn) return;
    
    bulkEnableBtn.addEventListener('click', function() {
        const selected = getSelectedTagIds();
        if (selected.length === 0) return;
        
        if (!confirm(`确定要启用选中的 ${selected.length} 个标签吗？`)) return;
        
        performBulkAction('enable', selected);
    });
}

// 批量禁用
export function initBulkDisable() {
    const bulkDisableBtn = document.getElementById('bulk-disable');
    if (!bulkDisableBtn) return;
    
    bulkDisableBtn.addEventListener('click', function() {
        const selected = getSelectedTagIds();
        if (selected.length === 0) return;
        
        if (!confirm(`确定要禁用选中的 ${selected.length} 个标签吗？`)) return;
        
        performBulkAction('disable', selected);
    });
}

// 批量删除
export function initBulkDelete() {
    const bulkDeleteBtn = document.getElementById('bulk-delete');
    if (!bulkDeleteBtn) return;
    
    bulkDeleteBtn.addEventListener('click', function() {
        const selected = getSelectedTagIds();
        if (selected.length === 0) return;
        
        if (!confirm(`确定要删除选中的 ${selected.length} 个标签吗？此操作不可恢复！`)) return;
        
        performBulkAction('delete', selected);
    });
}

// 获取选中的标签ID
function getSelectedTagIds() {
    const selected = document.querySelectorAll('.tag-checkbox:checked');
    return Array.from(selected).map(checkbox => checkbox.value);
}

// 执行批量操作
function performBulkAction(action, tagIds) {
    const bulkActions = document.getElementById('bulk-actions');
    bulkActions.classList.add('loading');
    
    fetch(`/admin/tags/bulk-${action}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ tag_ids: tagIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(data.message || '操作失败', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('网络错误', 'error');
    })
    .finally(() => {
        bulkActions.classList.remove('loading');
    });
}

// 颜色调色板功能
export function initColorPalette() {
    const colorPalette = document.querySelector('.color-palette');
    if (!colorPalette) return;
    
    const colorInput = document.getElementById('color');
    const colorPreview = document.getElementById('color-preview');
    
    colorPalette.addEventListener('click', function(e) {
        const colorItem = e.target.closest('.color-palette-item');
        if (!colorItem) return;
        
        const color = colorItem.dataset.color;
        
        // 更新表单
        if (colorInput) {
            colorInput.value = color;
        }
        
        // 更新预览
        if (colorPreview) {
            colorPreview.style.backgroundColor = color;
        }
        
        // 更新选择状态
        colorPalette.querySelectorAll('.color-palette-item').forEach(item => {
            item.classList.remove('selected');
        });
        colorItem.classList.add('selected');
    });
}

// 随机颜色生成器
export function initColorGenerator() {
    const generateBtn = document.querySelector('.color-generator-btn');
    if (!generateBtn) return;
    
    const colorInput = document.getElementById('color');
    const colorPreview = document.getElementById('color-preview');
    const generatorPreview = document.querySelector('.color-generator-preview');
    
    generateBtn.addEventListener('click', function() {
        const randomColor = generateRandomColor();
        
        // 更新预览
        if (generatorPreview) {
            generatorPreview.style.backgroundColor = randomColor;
        }
        
        // 动画效果
        if (generatorPreview) {
            generatorPreview.style.transform = 'scale(1.1)';
            setTimeout(() => {
                generatorPreview.style.transform = 'scale(1)';
            }, 200);
        }
        
        // 更新表单
        if (colorInput) {
            colorInput.value = randomColor;
        }
        
        if (colorPreview) {
            colorPreview.style.backgroundColor = randomColor;
        }
    });
}

// 生成随机颜色
function generateRandomColor() {
    const colors = [
        '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57',
        '#ff9ff3', '#54a0ff', '#5f27cd', '#00d2d3', '#ff9f43',
        '#6c5ce7', '#fd79a8', '#fdcb6e', '#6c5ce7', '#74b9ff',
        '#0984e3', '#00b894', '#00cec9', '#e84393', '#fd79a8'
    ];
    
    return colors[Math.floor(Math.random() * colors.length)];
}

// 表格行选择高亮
export function initRowSelection() {
    document.querySelectorAll('.tag-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.checked) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        });
    });
}

// 搜索结果高亮
export function initSearchHighlight() {
    const searchInput = document.querySelector('input[name="search"]');
    if (!searchInput || !searchInput.value) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    
    tableRows.forEach(row => {
        const textContent = row.textContent.toLowerCase();
        if (textContent.includes(searchTerm)) {
            highlightText(row, searchTerm);
        }
    });
}

function highlightText(element, searchTerm) {
    const walker = document.createTreeWalker(
        element,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );
    
    const textNodes = [];
    let node;
    
    while (node = walker.nextNode()) {
        textNodes.push(node);
    }
    
    textNodes.forEach(textNode => {
        const text = textNode.textContent;
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        
        if (regex.test(text)) {
            const highlightedText = text.replace(regex, '<span class="search-highlight">$1</span>');
            const wrapper = document.createElement('span');
            wrapper.innerHTML = highlightedText;
            textNode.parentNode.replaceChild(wrapper, textNode);
        }
    });
}

// 标签状态切换功能
export function initTagStatusToggle() {
    // 确保只在标签页面运行
    const currentPath = window.location.pathname;
    if (!currentPath.includes('/admin/tags')) {
        return;
    }
    
    let tagToggleTimeout = null;
    
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        // 确保开关初始状态不被禁用
        toggle.disabled = false;
        
        // 移除可能存在的旧监听器
        toggle.removeEventListener('change', toggle._statusToggleHandler);
        
        // 标记这个元素已被tags.js处理
        toggle.setAttribute('data-handler', 'tags');
        
        // 创建新的处理函数
        toggle._statusToggleHandler = function(event) {
            // 阻止事件冒泡，防止其他处理器执行
            event.stopPropagation();
            event.stopImmediatePropagation();
            
            const id = this.dataset.id;
            const isActive = this.checked;
            const toggleElement = this;
            
            // 检查是否已经在处理中
            if (tagToggleTimeout) {
                return;
            }
            
            // 确保开关可用
            if (toggleElement.disabled) {
                toggleElement.disabled = false;
            }
            
            // 清除之前的timeout
            if (tagToggleTimeout) {
                clearTimeout(tagToggleTimeout);
            }
            
            // 禁用开关，显示加载状态
            toggleElement.disabled = true;
            const slider = toggleElement.nextElementSibling;
            slider.style.opacity = '0.6';
            
            tagToggleTimeout = setTimeout(() => {
                const url = `/admin/tags/${id}/toggle-status`;
                const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfTokenElement?.getAttribute('content');
                
                if (!csrfToken) {
                    showMessage('安全令牌缺失，请刷新页面', 'error');
                    toggleElement.checked = !isActive;
                    toggleElement.disabled = false;
                    const slider = toggleElement.nextElementSibling;
                    slider.style.opacity = '1';
                    tagToggleTimeout = null;
                    return;
                }
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ is_active: isActive }),
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showMessage('状态更新成功', 'success');
                        
                        // 添加标签特有的视觉效果
                        const row = toggleElement.closest('tr');
                        const tagPreview = row.querySelector('.tag-preview');
                        
                        if (isActive) {
                            row.style.opacity = '1';
                            if (tagPreview) {
                                tagPreview.style.opacity = '1';
                            }
                        } else {
                            row.style.opacity = '0.6';
                            if (tagPreview) {
                                tagPreview.style.opacity = '0.6';
                            }
                        }
                    } else {
                        toggleElement.checked = !isActive;
                        showMessage(data.message || '状态更新失败', 'error');
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
                    tagToggleTimeout = null;
                });
            }, 300); // 300ms 防抖
        };
        
        // 添加事件监听器
        toggle.addEventListener('change', toggle._statusToggleHandler);
    });
}

// 初始化所有tags模块功能
export function initTagsModule() {
    initTagColors();
    initSelectAll();
    initIndividualSelect();
    initBulkEnable();
    initBulkDisable();
    initBulkDelete();
    initColorPalette();
    initColorGenerator();
    initRowSelection();
    initSearchHighlight();
    initTagStatusToggle();
    initDeleteConfirmation();
    initSlugGeneration();
    initCharCounter();
    initFormValidation();
}

// 将函数暴露到全局作用域（用于兼容性）
if (typeof window !== 'undefined') {
    window.updateBulkActions = updateBulkActions;
    window.generateRandomColor = generateRandomColor;
}

// 页面加载完成后自动初始化
document.addEventListener('DOMContentLoaded', function() {
    initTagsModule();
}); 