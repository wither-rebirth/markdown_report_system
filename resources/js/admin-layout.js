// Admin Layout JavaScript - 布局相关功能模块

/**
 * 管理后台布局功能类
 */
class AdminLayout {
    constructor() {
        this.config = {
            sidebarBreakpoint: 1024,
            autoHideAlertDuration: 5000,
            animationDuration: 300
        };
        
        this.elements = {};
        this.init();
    }

    /**
     * 初始化
     */
    init() {
        this.cacheElements();
        this.bindEvents();
        this.setupAnimations();
        this.initializeComponents();
        
        console.log('🎨 Admin Layout 已初始化');
    }

    /**
     * 缓存 DOM 元素
     */
    cacheElements() {
        this.elements = {
            body: document.body,
            sidebarToggle: document.getElementById('sidebarToggle'),
            sidebarClose: document.getElementById('sidebarClose'),
            sidebar: document.getElementById('adminSidebar'),
            overlay: document.getElementById('mobileOverlay'),
            userDropdownToggle: document.getElementById('userDropdownToggle'),
            userDropdownMenu: document.getElementById('userDropdownMenu'),
            content: document.querySelector('.admin-content')
        };
    }

    /**
     * 绑定事件监听器
     */
    bindEvents() {
        this.setupSidebarEvents();
        this.setupUserMenuEvents();
        this.setupConfirmationEvents();
        this.setupKeyboardEvents();
        this.setupWindowEvents();
    }

    /**
     * 设置侧边栏事件
     */
    setupSidebarEvents() {
        const { sidebarToggle, sidebarClose, overlay } = this.elements;

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleSidebar();
            });
        }

        if (sidebarClose) {
            sidebarClose.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeSidebar();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                this.closeSidebar();
            });
        }
    }

    /**
     * 设置用户菜单事件
     */
    setupUserMenuEvents() {
        const { userDropdownToggle, userDropdownMenu } = this.elements;

        if (userDropdownToggle && userDropdownMenu) {
            userDropdownToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleUserMenu();
            });

            // 点击其他地方关闭下拉菜单
            document.addEventListener('click', (e) => {
                if (!userDropdownMenu.contains(e.target) && !userDropdownToggle.contains(e.target)) {
                    this.closeUserMenu();
                }
            });
        }
    }

    /**
     * 设置确认对话框事件
     */
    setupConfirmationEvents() {
        document.addEventListener('click', (e) => {
            const confirmElement = e.target.closest('[data-confirm]');
            if (confirmElement) {
                const message = confirmElement.dataset.confirm;
                if (!confirm(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        });
    }

    /**
     * 设置键盘事件
     */
    setupKeyboardEvents() {
        document.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'Escape':
                    this.handleEscapeKey();
                    break;
                case 'Enter':
                case ' ':
                    this.handleEnterSpaceKey(e);
                    break;
            }
        });
    }

    /**
     * 设置窗口事件
     */
    setupWindowEvents() {
        window.addEventListener('resize', () => {
            this.handleWindowResize();
        });
    }

    /**
     * 切换侧边栏
     */
    toggleSidebar() {
        const { sidebar } = this.elements;
        const isOpen = sidebar.classList.contains('mobile-open');
        
        if (isOpen) {
            this.closeSidebar();
        } else {
            this.openSidebar();
        }
    }

    /**
     * 打开侧边栏
     */
    openSidebar() {
        const { sidebar, overlay, body } = this.elements;
        
        sidebar.classList.add('mobile-open');
        overlay.classList.add('active');
        body.classList.add('sidebar-open');
        body.style.overflow = 'hidden';
    }

    /**
     * 关闭侧边栏
     */
    closeSidebar() {
        const { sidebar, overlay, body } = this.elements;
        
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        body.classList.remove('sidebar-open');
        body.style.overflow = '';
    }

    /**
     * 切换用户菜单
     */
    toggleUserMenu() {
        const { userDropdownMenu } = this.elements;
        const isOpen = userDropdownMenu.classList.contains('active');
        
        if (isOpen) {
            this.closeUserMenu();
        } else {
            this.openUserMenu();
        }
    }

    /**
     * 打开用户菜单
     */
    openUserMenu() {
        const { userDropdownToggle, userDropdownMenu } = this.elements;
        
        userDropdownMenu.classList.add('active');
        userDropdownToggle.setAttribute('aria-expanded', 'true');
    }

    /**
     * 关闭用户菜单
     */
    closeUserMenu() {
        const { userDropdownToggle, userDropdownMenu } = this.elements;
        
        userDropdownMenu.classList.remove('active');
        userDropdownToggle.setAttribute('aria-expanded', 'false');
    }

    /**
     * 处理 Escape 键
     */
    handleEscapeKey() {
        const { sidebar, userDropdownMenu } = this.elements;
        
        if (sidebar.classList.contains('mobile-open')) {
            this.closeSidebar();
        } else if (userDropdownMenu.classList.contains('active')) {
            this.closeUserMenu();
        }
    }

    /**
     * 处理 Enter/Space 键
     */
    handleEnterSpaceKey(e) {
        const { userDropdownToggle } = this.elements;
        
        if (e.target === userDropdownToggle) {
            e.preventDefault();
            this.toggleUserMenu();
        }
    }

    /**
     * 处理窗口大小变化
     */
    handleWindowResize() {
        if (window.innerWidth > this.config.sidebarBreakpoint) {
            this.closeSidebar();
        }
    }

    /**
     * 设置动画效果
     */
    setupAnimations() {
        // 页面加载完成动画
        this.elements.body.classList.add('loaded');
        
        // 自动隐藏消息提示
        this.setupAutoHideAlerts();
    }

    /**
     * 设置自动隐藏提示
     */
    setupAutoHideAlerts() {
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => this.hideAlert(alert));
        }, this.config.autoHideAlertDuration);
    }

    /**
     * 隐藏提示消息
     */
    hideAlert(alert) {
        if (!alert) return;
        
        alert.classList.add('removing');
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, this.config.animationDuration);
    }

    /**
     * 初始化组件
     */
    initializeComponents() {
        // 设置工具提示
        this.setupTooltips();
        
        // 设置表单增强
        this.setupFormEnhancements();
        
        // 设置数据表格
        this.setupDataTables();
    }

    /**
     * 设置工具提示
     */
    setupTooltips() {
        const tooltipElements = document.querySelectorAll('[title], [data-tooltip]');
        
        tooltipElements.forEach(element => {
            const text = element.getAttribute('title') || element.getAttribute('data-tooltip');
            if (text) {
                element.setAttribute('data-tooltip', text);
                element.removeAttribute('title');
                
                let showTimeout;
                
                element.addEventListener('mouseenter', () => {
                    // 添加800ms延迟，避免频繁显示
                    showTimeout = setTimeout(() => {
                        this.showTooltip(element, text);
                    }, 800);
                });
                
                element.addEventListener('mouseleave', () => {
                    // 清除显示延迟
                    if (showTimeout) {
                        clearTimeout(showTimeout);
                        showTimeout = null;
                    }
                    // 隐藏已显示的提示框
                    this.hideTooltip();
                });
            }
        });
    }

    /**
     * 显示工具提示
     */
    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip-popup';
        tooltip.textContent = text;
        tooltip.id = 'admin-tooltip';
        
        document.body.appendChild(tooltip);
        
        // 定位工具提示
        const rect = element.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        tooltip.style.left = `${rect.left + (rect.width - tooltipRect.width) / 2}px`;
        tooltip.style.top = `${rect.top - tooltipRect.height - 10}px`;
        
        // 动画显示
        requestAnimationFrame(() => {
            tooltip.style.opacity = '1';
            tooltip.style.transform = 'translateY(0)';
        });
    }

    /**
     * 隐藏工具提示
     */
    hideTooltip() {
        const tooltip = document.getElementById('admin-tooltip');
        if (tooltip) {
            tooltip.style.opacity = '0';
            tooltip.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (tooltip.parentNode) {
                    tooltip.parentNode.removeChild(tooltip);
                }
            }, 200);
        }
    }

    /**
     * 设置表单增强
     */
    setupFormEnhancements() {
        // 表单验证
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            this.enhanceForm(form);
        });
    }

    /**
     * 增强表单
     */
    enhanceForm(form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // 添加焦点效果
            input.addEventListener('focus', () => {
                input.closest('.form-group')?.classList.add('focused');
            });
            
            input.addEventListener('blur', () => {
                input.closest('.form-group')?.classList.remove('focused');
            });
        });
    }

    /**
     * 设置数据表格
     */
    setupDataTables() {
        const tables = document.querySelectorAll('.data-table');
        
        tables.forEach(table => {
            this.enhanceTable(table);
        });
    }

    /**
     * 增强数据表格
     */
    enhanceTable(table) {
        // 添加排序功能
        const headers = table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sortTable(table, header);
            });
        });
        
        // 添加行选择功能
        const checkboxes = table.querySelectorAll('input[type="checkbox"]');
        if (checkboxes.length > 0) {
            this.setupTableSelection(table, checkboxes);
        }
    }

    /**
     * 表格排序
     */
    sortTable(table, header) {
        // 简单的客户端排序实现
        const index = Array.from(header.parentNode.children).indexOf(header);
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isAscending = !header.classList.contains('sort-asc');
        
        // 清除其他列的排序状态
        header.parentNode.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        
        // 设置当前列的排序状态
        header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
        
        // 排序行
        rows.sort((a, b) => {
            const aText = a.children[index].textContent.trim();
            const bText = b.children[index].textContent.trim();
            
            if (isAscending) {
                return aText.localeCompare(bText);
            } else {
                return bText.localeCompare(aText);
            }
        });
        
        // 重新排列行
        rows.forEach(row => tbody.appendChild(row));
    }

    /**
     * 设置表格选择
     */
    setupTableSelection(table, checkboxes) {
        const selectAll = table.querySelector('input[type="checkbox"][data-select-all]');
        
        if (selectAll) {
            selectAll.addEventListener('change', () => {
                checkboxes.forEach(checkbox => {
                    if (checkbox !== selectAll) {
                        checkbox.checked = selectAll.checked;
                    }
                });
                this.updateBatchActions();
            });
        }
        
        checkboxes.forEach(checkbox => {
            if (checkbox !== selectAll) {
                checkbox.addEventListener('change', () => {
                    this.updateBatchActions();
                });
            }
        });
    }

    /**
     * 更新批量操作
     */
    updateBatchActions() {
        const selected = document.querySelectorAll('input[type="checkbox"]:checked').length;
        const batchActions = document.querySelector('.batch-actions');
        
        if (batchActions) {
            if (selected > 0) {
                batchActions.style.display = 'flex';
                batchActions.querySelector('.selected-count').textContent = selected;
            } else {
                batchActions.style.display = 'none';
            }
        }
    }
}

/**
 * 消息提示功能
 */
class AdminMessages {
    constructor() {
        this.container = document.querySelector('.admin-content');
    }

    /**
     * 显示消息
     */
    show(message, type = 'success', duration = 5000) {
        const alertTypes = {
            success: { icon: 'fas fa-check-circle', title: '操作成功' },
            error: { icon: 'fas fa-exclamation-triangle', title: '操作失败' },
            warning: { icon: 'fas fa-exclamation-circle', title: '警告' },
            info: { icon: 'fas fa-info-circle', title: '提示' }
        };

        const alertInfo = alertTypes[type] || alertTypes.info;

        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${type}`;
        alertElement.setAttribute('role', 'alert');
        
        alertElement.innerHTML = `
            <div class="alert-icon">
                <i class="${alertInfo.icon}"></i>
            </div>
            <div class="alert-content">
                <div class="alert-title">${alertInfo.title}</div>
                <div class="alert-message">${message}</div>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        if (this.container) {
            this.container.insertBefore(alertElement, this.container.firstChild);
            
            // 自动隐藏
            if (duration > 0) {
                setTimeout(() => {
                    if (alertElement.parentNode) {
                        alertElement.classList.add('removing');
                        setTimeout(() => {
                            if (alertElement.parentNode) {
                                alertElement.parentNode.removeChild(alertElement);
                            }
                        }, 300);
                    }
                }, duration);
            }
        }

        return alertElement;
    }
}

// 导出功能
window.AdminLayout = AdminLayout;
window.AdminMessages = AdminMessages;

// 全局消息提示函数（保持向后兼容）
window.showMessage = function(message, type = 'success') {
    if (!window.adminMessages) {
        window.adminMessages = new AdminMessages();
    }
    return window.adminMessages.show(message, type);
};

// 在 DOM 加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    window.adminLayout = new AdminLayout();
});

export { AdminLayout, AdminMessages }; 