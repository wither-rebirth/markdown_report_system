// 管理端JavaScript - 现代化优化版本

// 全局变量
const AdminApp = {
    // 配置
    config: {
        animation: {
            duration: 300,
            easing: 'ease-in-out'
        },
        toast: {
            duration: 5000,
            position: 'top-right'
        }
    },
    
    // 初始化
    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupAnimations();
        console.log('🚀 管理端应用已初始化');
    },
    
    // 设置事件监听器
    setupEventListeners() {
        // 侧边栏相关
        this.setupSidebar();
        
        // 用户菜单相关
        this.setupUserMenu();
        
        // 表单相关
        this.setupForms();
        
        // 数据表格相关
        this.setupDataTables();
        
        // 通用交互
        this.setupGeneralInteractions();
    },
    
    // 侧边栏功能
    setupSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        const body = document.body;
        
        // 切换侧边栏
        const toggleSidebar = () => {
            const isOpen = sidebar.classList.contains('mobile-open');
            
            if (isOpen) {
                this.closeSidebar();
            } else {
                this.openSidebar();
            }
        };
        
        // 事件绑定
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }
        
        if (sidebarClose) {
            sidebarClose.addEventListener('click', () => this.closeSidebar());
        }
        
        if (overlay) {
            overlay.addEventListener('click', () => this.closeSidebar());
        }
        
        // 键盘快捷键
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
                this.closeSidebar();
            }
        });
        
        // 窗口大小变化时的处理
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                this.closeSidebar();
            }
        });
    },
    
    // 打开侧边栏
    openSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        sidebar.classList.add('mobile-open');
        overlay.classList.add('active');
        document.body.classList.add('sidebar-open');
        
        // 禁用页面滚动
        document.body.style.overflow = 'hidden';
    },
    
    // 关闭侧边栏
    closeSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        
        // 恢复页面滚动
        document.body.style.overflow = '';
    },
    
    // 用户菜单功能
    setupUserMenu() {
        const dropdownToggle = document.getElementById('userDropdownToggle');
        const dropdownMenu = document.getElementById('userDropdownMenu');
        
        if (!dropdownToggle || !dropdownMenu) return;
        
        // 切换下拉菜单
        dropdownToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdownMenu.classList.contains('active');
            
            if (isOpen) {
                this.closeUserMenu();
            } else {
                this.openUserMenu();
            }
        });
        
        // 点击其他地方关闭菜单
        document.addEventListener('click', () => {
            this.closeUserMenu();
        });
        
        // 键盘导航
        dropdownToggle.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                dropdownToggle.click();
            }
        });
    },
    
    // 打开用户菜单
    openUserMenu() {
        const dropdownToggle = document.getElementById('userDropdownToggle');
        const dropdownMenu = document.getElementById('userDropdownMenu');
        
        dropdownMenu.classList.add('active');
        dropdownToggle.setAttribute('aria-expanded', 'true');
    },
    
    // 关闭用户菜单
    closeUserMenu() {
        const dropdownToggle = document.getElementById('userDropdownToggle');
        const dropdownMenu = document.getElementById('userDropdownMenu');
        
        if (dropdownMenu) {
            dropdownMenu.classList.remove('active');
        }
        
        if (dropdownToggle) {
            dropdownToggle.setAttribute('aria-expanded', 'false');
        }
    },
    
    // 表单功能
    setupForms() {
        // 表单验证
        this.setupFormValidation();
        
        // 自动保存
        this.setupAutoSave();
        
        // 表单增强
        this.enhanceForms();
    },
    
    // 表单验证
    setupFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
            
            // 实时验证
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
                
                input.addEventListener('input', () => {
                    this.clearFieldError(input);
                });
            });
        });
    },
    
    // 验证表单
    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    // 验证字段
    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.getAttribute('data-name') || field.name || '此字段';
        let isValid = true;
        let errorMessage = '';
        
        // 必填验证
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = `${fieldName}不能为空`;
        }
        
        // 邮箱验证
        if (field.type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            errorMessage = '请输入有效的邮箱地址';
        }
        
        // 最小长度验证
        const minLength = field.getAttribute('minlength');
        if (minLength && value.length < parseInt(minLength)) {
            isValid = false;
            errorMessage = `${fieldName}至少需要${minLength}个字符`;
        }
        
        // 显示或清除错误
        if (isValid) {
            this.clearFieldError(field);
        } else {
            this.showFieldError(field, errorMessage);
        }
        
        return isValid;
    },
    
    // 显示字段错误
    showFieldError(field, message) {
        this.clearFieldError(field);
        
        field.classList.add('error');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        
        field.parentNode.appendChild(errorElement);
    },
    
    // 清除字段错误
    clearFieldError(field) {
        field.classList.remove('error');
        
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    },
    
    // 邮箱验证
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },
    
    // 自动保存功能
    setupAutoSave() {
        const autoSaveForms = document.querySelectorAll('form[data-autosave]');
        
        autoSaveForms.forEach(form => {
            let saveTimeout;
            
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        this.autoSaveForm(form);
                    }, 2000);
                });
            });
        });
    },
    
    // 执行自动保存
    autoSaveForm(form) {
        const formData = new FormData(form);
        const url = form.getAttribute('data-autosave-url');
        
        if (!url) return;
        
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast('内容已自动保存', 'success', 2000);
            }
        })
        .catch(error => {
            console.warn('自动保存失败:', error);
        });
    },
    
    // 增强表单功能
    enhanceForms() {
        // 文件上传预览
        this.setupFilePreview();
        
        // 字符计数
        this.setupCharacterCount();
        
        // 标签输入
        this.setupTagInput();
    },
    
    // 文件上传预览
    setupFilePreview() {
        const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;
                
                const previewContainer = document.getElementById(input.getAttribute('data-preview'));
                if (!previewContainer) return;
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewContainer.innerHTML = `
                            <img src="${e.target.result}" 
                                 style="max-width: 200px; max-height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" 
                                 alt="预览图片">
                        `;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    },
    
    // 字符计数
    setupCharacterCount() {
        const textareas = document.querySelectorAll('textarea[data-max-length]');
        
        textareas.forEach(textarea => {
            const maxLength = parseInt(textarea.getAttribute('data-max-length'));
            
            // 创建计数器
            const counter = document.createElement('div');
            counter.className = 'character-counter';
            counter.style.cssText = `
                font-size: 0.75rem;
                color: var(--gray-500);
                text-align: right;
                margin-top: 4px;
            `;
            
            textarea.parentNode.appendChild(counter);
            
            // 更新计数
            const updateCounter = () => {
                const currentLength = textarea.value.length;
                counter.textContent = `${currentLength}/${maxLength}`;
                
                if (currentLength > maxLength * 0.9) {
                    counter.style.color = 'var(--warning-color)';
                } else {
                    counter.style.color = 'var(--gray-500)';
                }
                
                if (currentLength > maxLength) {
                    counter.style.color = 'var(--danger-color)';
                    textarea.style.borderColor = 'var(--danger-color)';
                } else {
                    textarea.style.borderColor = '';
                }
            };
            
            textarea.addEventListener('input', updateCounter);
            updateCounter(); // 初始化
        });
    },
    
    // 标签输入功能
    setupTagInput() {
        const tagInputs = document.querySelectorAll('[data-tag-input]');
        
        tagInputs.forEach(container => {
            const input = container.querySelector('input');
            const tagsContainer = container.querySelector('.tags-list') || this.createTagsContainer(container);
            
            if (!input) return;
            
            let tags = [];
            
            // 处理输入
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const value = input.value.trim();
                    
                    if (value && !tags.includes(value)) {
                        tags.push(value);
                        this.addTag(tagsContainer, value, () => {
                            tags = tags.filter(tag => tag !== value);
                            this.updateTagsInput(container, tags);
                        });
                        input.value = '';
                        this.updateTagsInput(container, tags);
                    }
                } else if (e.key === 'Backspace' && input.value === '' && tags.length > 0) {
                    const lastTag = tags.pop();
                    tagsContainer.querySelector(`[data-tag="${lastTag}"]`).remove();
                    this.updateTagsInput(container, tags);
                }
            });
        });
    },
    
    // 创建标签容器
    createTagsContainer(container) {
        const tagsContainer = document.createElement('div');
        tagsContainer.className = 'tags-list';
        tagsContainer.style.cssText = `
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 8px;
        `;
        
        container.insertBefore(tagsContainer, container.firstChild);
        return tagsContainer;
    },
    
    // 添加标签
    addTag(container, text, onRemove) {
        const tag = document.createElement('span');
        tag.className = 'tag-item';
        tag.setAttribute('data-tag', text);
        tag.style.cssText = `
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            background: var(--primary-light);
            color: var(--primary-color);
            border-radius: 4px;
            font-size: 0.75rem;
            gap: 4px;
        `;
        
        tag.innerHTML = `
            ${text}
            <button type="button" style="background: none; border: none; color: inherit; cursor: pointer; padding: 0;">×</button>
        `;
        
        tag.querySelector('button').addEventListener('click', () => {
            tag.remove();
            onRemove();
        });
        
        container.appendChild(tag);
    },
    
    // 更新标签隐藏输入
    updateTagsInput(container, tags) {
        let hiddenInput = container.querySelector('input[type="hidden"]');
        
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = container.getAttribute('data-name') || 'tags';
            container.appendChild(hiddenInput);
        }
        
        hiddenInput.value = tags.join(',');
    },
    
    // 数据表格功能
    setupDataTables() {
        // 确认删除
        this.setupDeleteConfirmation();
        
        // 批量操作
        this.setupBatchActions();
        
        // 排序功能
        this.setupSorting();
    },
    
    // 删除确认
    setupDeleteConfirmation() {
        document.addEventListener('click', (e) => {
            const deleteBtn = e.target.closest('[data-confirm]');
            if (!deleteBtn) return;
            
            e.preventDefault();
            
            const message = deleteBtn.getAttribute('data-confirm');
            const confirmTitle = deleteBtn.getAttribute('data-confirm-title') || '确认删除';
            
            this.showConfirmDialog(confirmTitle, message, () => {
                // 如果是表单按钮，提交表单
                if (deleteBtn.type === 'submit') {
                    deleteBtn.form.submit();
                } else if (deleteBtn.href) {
                    window.location.href = deleteBtn.href;
                }
            });
        });
    },
    
    // 批量操作
    setupBatchActions() {
        const batchForms = document.querySelectorAll('[data-batch-form]');
        
        batchForms.forEach(form => {
            const checkboxes = form.querySelectorAll('input[type="checkbox"][name="ids[]"]');
            const selectAllCheckbox = form.querySelector('input[type="checkbox"][data-select-all]');
            const batchActions = form.querySelectorAll('[data-batch-action]');
            
            // 全选功能
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', () => {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                    this.updateBatchActions(batchActions, this.getSelectedIds(checkboxes));
                });
            }
            
            // 单选变化
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const selectedIds = this.getSelectedIds(checkboxes);
                    this.updateBatchActions(batchActions, selectedIds);
                    
                    // 更新全选状态
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = selectedIds.length === checkboxes.length;
                        selectAllCheckbox.indeterminate = selectedIds.length > 0 && selectedIds.length < checkboxes.length;
                    }
                });
            });
        });
    },
    
    // 获取选中的ID
    getSelectedIds(checkboxes) {
        return Array.from(checkboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);
    },
    
    // 更新批量操作按钮状态
    updateBatchActions(actions, selectedIds) {
        actions.forEach(action => {
            if (selectedIds.length > 0) {
                action.removeAttribute('disabled');
                action.classList.remove('disabled');
            } else {
                action.setAttribute('disabled', 'disabled');
                action.classList.add('disabled');
            }
        });
    },
    
    // 排序功能
    setupSorting() {
        const sortableHeaders = document.querySelectorAll('[data-sortable]');
        
        sortableHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-sortable');
                const currentSort = new URLSearchParams(window.location.search).get('sort');
                const currentOrder = new URLSearchParams(window.location.search).get('order');
                
                let newOrder = 'asc';
                if (currentSort === column && currentOrder === 'asc') {
                    newOrder = 'desc';
                }
                
                const url = new URL(window.location);
                url.searchParams.set('sort', column);
                url.searchParams.set('order', newOrder);
                
                window.location.href = url.toString();
            });
        });
    },
    
    // 通用交互
    setupGeneralInteractions() {
        // 工具提示
        this.setupTooltips();
        
        // 加载状态
        this.setupLoadingStates();
        
        // 自动隐藏消息
        this.setupAutoHideAlerts();
        
        // 平滑滚动
        this.setupSmoothScroll();
    },
    
    // 工具提示
    setupTooltips() {
        const elements = document.querySelectorAll('[title], [data-tooltip]');
        
        elements.forEach(element => {
            const text = element.getAttribute('data-tooltip') || element.getAttribute('title');
            if (!text) return;
            
            // 移除原生title避免冲突
            element.removeAttribute('title');
            
            let tooltip;
            
            element.addEventListener('mouseenter', () => {
                tooltip = this.createTooltip(text);
                document.body.appendChild(tooltip);
                this.positionTooltip(tooltip, element);
            });
            
            element.addEventListener('mouseleave', () => {
                if (tooltip) {
                    tooltip.remove();
                    tooltip = null;
                }
            });
        });
    },
    
    // 创建工具提示
    createTooltip(text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: var(--gray-800);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            z-index: 9999;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
            max-width: 200px;
            word-wrap: break-word;
        `;
        
        // 添加箭头
        const arrow = document.createElement('div');
        arrow.style.cssText = `
            position: absolute;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid var(--gray-800);
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
        `;
        tooltip.appendChild(arrow);
        
        // 显示动画
        setTimeout(() => {
            tooltip.style.opacity = '1';
        }, 10);
        
        return tooltip;
    },
    
    // 定位工具提示
    positionTooltip(tooltip, element) {
        const rect = element.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        const left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        const top = rect.top - tooltipRect.height - 10;
        
        tooltip.style.left = Math.max(10, Math.min(left, window.innerWidth - tooltipRect.width - 10)) + 'px';
        tooltip.style.top = Math.max(10, top) + 'px';
    },
    
    // 加载状态
    setupLoadingStates() {
        // 表单提交时显示加载状态
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const submitBtn = form.querySelector('[type="submit"]');
            
            if (submitBtn && !submitBtn.hasAttribute('data-no-loading')) {
                this.setButtonLoading(submitBtn, true);
            }
        });
        
        // AJAX请求时的加载状态
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            const button = document.activeElement;
            if (button && button.hasAttribute('data-loading')) {
                AdminApp.setButtonLoading(button, true);
            }
            
            return originalFetch.apply(this, args).finally(() => {
                if (button && button.hasAttribute('data-loading')) {
                    AdminApp.setButtonLoading(button, false);
                }
            });
        };
    },
    
    // 设置按钮加载状态
    setButtonLoading(button, loading) {
        if (loading) {
            button.setAttribute('data-original-text', button.innerHTML);
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 处理中...';
            button.disabled = true;
        } else {
            const originalText = button.getAttribute('data-original-text');
            if (originalText) {
                button.innerHTML = originalText;
                button.removeAttribute('data-original-text');
            }
            button.disabled = false;
        }
    },
    
    // 自动隐藏消息
    setupAutoHideAlerts() {
        const alerts = document.querySelectorAll('.alert');
        
        alerts.forEach(alert => {
            // 如果有关闭按钮，设置点击事件
            const closeBtn = alert.querySelector('.alert-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    this.hideAlert(alert);
                });
            }
            
            // 自动隐藏
            if (!alert.hasAttribute('data-no-auto-hide')) {
                setTimeout(() => {
                    this.hideAlert(alert);
                }, this.config.toast.duration);
            }
        });
    },
    
    // 隐藏消息
    hideAlert(alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            alert.remove();
        }, this.config.animation.duration);
    },
    
    // 平滑滚动
    setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    },
    
    // 初始化组件
    initializeComponents() {
        // 初始化图表
        this.initializeCharts();
        
        // 初始化开关
        this.initializeSwitches();
        
        // 初始化日期选择器
        this.initializeDatePickers();
    },
    
    // 初始化图表
    initializeCharts() {
        const chartElements = document.querySelectorAll('[data-chart]');
        
        chartElements.forEach(element => {
            const chartType = element.getAttribute('data-chart');
            const chartData = JSON.parse(element.getAttribute('data-chart-data') || '{}');
            
            // 根据类型创建不同的图表
            switch (chartType) {
                case 'line':
                    this.createLineChart(element, chartData);
                    break;
                case 'bar':
                    this.createBarChart(element, chartData);
                    break;
                case 'pie':
                    this.createPieChart(element, chartData);
                    break;
            }
        });
    },
    
    // 创建折线图
    createLineChart(element, data) {
        // 这里可以集成Chart.js或其他图表库
        console.log('创建折线图:', element, data);
    },
    
    // 创建柱状图
    createBarChart(element, data) {
        console.log('创建柱状图:', element, data);
    },
    
    // 创建饼图
    createPieChart(element, data) {
        console.log('创建饼图:', element, data);
    },
    
    // 初始化开关
    initializeSwitches() {
        const switches = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
        
        switches.forEach(switchEl => {
            switchEl.addEventListener('change', () => {
                const url = switchEl.getAttribute('data-url');
                const id = switchEl.getAttribute('data-id');
                const isChecked = switchEl.checked;
                
                if (url && id) {
                    this.updateToggleState(url, id, isChecked, switchEl);
                }
            });
        });
    },
    
    // 更新开关状态
    updateToggleState(url, id, isChecked, switchEl) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id: id,
                value: isChecked
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast('状态更新成功', 'success');
            } else {
                switchEl.checked = !isChecked; // 回滚
                this.showToast('状态更新失败', 'error');
            }
        })
        .catch(error => {
            switchEl.checked = !isChecked; // 回滚
            this.showToast('网络错误，请重试', 'error');
        });
    },
    
    // 初始化日期选择器
    initializeDatePickers() {
        const dateInputs = document.querySelectorAll('input[type="date"], input[data-datepicker]');
        
        dateInputs.forEach(input => {
            // 可以在这里集成更好的日期选择器库
            // 如flatpickr等
        });
    },
    
    // 设置动画
    setupAnimations() {
        // 页面加载动画
        this.setupPageLoadAnimation();
        
        // 滚动动画
        this.setupScrollAnimations();
        
        // 悬停效果
        this.setupHoverEffects();
    },
    
    // 页面加载动画
    setupPageLoadAnimation() {
        // 标记页面已加载
        setTimeout(() => {
            document.body.classList.add('loaded');
        }, 100);
        
        // 渐现动画
        const animatedElements = document.querySelectorAll('[data-animate]');
        animatedElements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.6s ease-out';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });
    },
    
    // 滚动动画
    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('[data-scroll-animate]').forEach(element => {
            observer.observe(element);
        });
    },
    
    // 悬停效果
    setupHoverEffects() {
        // 卡片悬停效果
        const cards = document.querySelectorAll('.card, .stat-card, .dashboard-widget');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-2px)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
    },
    
    // 显示Toast消息
    showToast(message, type = 'info', duration = null) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };
        
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="${icons[type] || icons.info}"></i>
            </div>
            <div class="toast-content">${message}</div>
            <button class="toast-close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // 样式
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--bg-primary);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: var(--spacing-lg);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease-out;
        `;
        
        // 类型颜色
        const colors = {
            success: 'var(--success-color)',
            error: 'var(--danger-color)',
            warning: 'var(--warning-color)',
            info: 'var(--info-color)'
        };
        
        toast.querySelector('.toast-icon').style.color = colors[type] || colors.info;
        
        // 关闭按钮
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.hideToast(toast);
        });
        
        // 添加到页面
        document.body.appendChild(toast);
        
        // 显示动画
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 10);
        
        // 自动隐藏
        setTimeout(() => {
            this.hideToast(toast);
        }, duration || this.config.toast.duration);
    },
    
    // 隐藏Toast
    hideToast(toast) {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    },
    
    // 显示确认对话框
    showConfirmDialog(title, message, onConfirm, onCancel = null) {
        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease-out;
        `;
        
        const dialog = document.createElement('div');
        dialog.className = 'confirm-dialog';
        dialog.style.cssText = `
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            max-width: 400px;
            width: 90%;
            transform: scale(0.8);
            transition: transform 0.3s ease-out;
        `;
        
        dialog.innerHTML = `
            <div class="confirm-header">
                <h3 style="margin: 0 0 var(--spacing-md) 0; color: var(--gray-800);">${title}</h3>
            </div>
            <div class="confirm-body">
                <p style="margin: 0 0 var(--spacing-xl) 0; color: var(--gray-600); line-height: 1.5;">${message}</p>
            </div>
            <div class="confirm-actions" style="display: flex; gap: var(--spacing-md); justify-content: flex-end;">
                <button class="btn btn-secondary confirm-cancel">取消</button>
                <button class="btn btn-danger confirm-ok">确认</button>
            </div>
        `;
        
        // 事件处理
        dialog.querySelector('.confirm-cancel').addEventListener('click', () => {
            this.hideConfirmDialog(overlay);
            if (onCancel) onCancel();
        });
        
        dialog.querySelector('.confirm-ok').addEventListener('click', () => {
            this.hideConfirmDialog(overlay);
            onConfirm();
        });
        
        // 点击遮罩关闭
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                this.hideConfirmDialog(overlay);
                if (onCancel) onCancel();
            }
        });
        
        // ESC键关闭
        const handleKeydown = (e) => {
            if (e.key === 'Escape') {
                this.hideConfirmDialog(overlay);
                if (onCancel) onCancel();
                document.removeEventListener('keydown', handleKeydown);
            }
        };
        document.addEventListener('keydown', handleKeydown);
        
        overlay.appendChild(dialog);
        document.body.appendChild(overlay);
        
        // 显示动画
        setTimeout(() => {
            overlay.style.opacity = '1';
            dialog.style.transform = 'scale(1)';
        }, 10);
    },
    
    // 隐藏确认对话框
    hideConfirmDialog(overlay) {
        overlay.style.opacity = '0';
        overlay.querySelector('.confirm-dialog').style.transform = 'scale(0.8)';
        
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    }
};

// DOM加载完成后初始化
document.addEventListener('DOMContentLoaded', () => {
    AdminApp.init();
});

// 导出到全局作用域
window.AdminApp = AdminApp; 