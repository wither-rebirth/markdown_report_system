// Admin JavaScript 入口文件

// 导入基础 admin 功能
import './admin.js';

// 导入布局相关功能
import { AdminLayout, AdminMessages } from './admin-layout.js';

// 导入样式文件
import '../css/admin.new.css';

/**
 * Admin 应用主入口
 */
class AdminApp {
    constructor() {
        this.layout = null;
        this.messages = null;
        this.modules = new Map();
        this.initialized = false;
    }

    /**
     * 初始化应用
     */
    async init() {
        if (this.initialized) return;

        try {
            // 初始化核心模块
            this.layout = new AdminLayout();
            this.messages = new AdminMessages();

            // 注册全局对象
            this.registerGlobals();

            // 加载页面特定模块
            await this.loadPageModules();

            // 设置错误处理
            this.setupErrorHandling();

            this.initialized = true;
            console.log('🚀 Admin 应用初始化完成');

        } catch (error) {
            console.error('❌ Admin 应用初始化失败:', error);
            this.showError('应用初始化失败，请刷新页面重试');
        }
    }

    /**
     * 注册全局对象
     */
    registerGlobals() {
        // 向后兼容的全局函数
        window.showMessage = (message, type = 'success') => {
            return this.messages.show(message, type);
        };

        window.AdminApp = this;
        window.AdminLayout = AdminLayout;
        window.AdminMessages = AdminMessages;
    }

    /**
     * 加载页面特定模块
     */
    async loadPageModules() {
        const currentPath = window.location.pathname;
        const modules = this.getModulesForPath(currentPath);

        for (const moduleName of modules) {
            try {
                await this.loadModule(moduleName);
            } catch (error) {
                console.warn(`模块 ${moduleName} 加载失败:`, error);
            }
        }
    }

    /**
     * 根据路径获取需要加载的模块
     */
    getModulesForPath(path) {
        const moduleMap = {
            '/admin/dashboard': ['dashboard', 'charts'],
            '/admin/analytics': ['charts']
            // 注意：其他页面模块已通过Vite直接加载，不需要动态导入
        };

        // 精确匹配
        if (moduleMap[path]) {
            return moduleMap[path];
        }

        // 模糊匹配
        for (const [pattern, modules] of Object.entries(moduleMap)) {
            if (path.startsWith(pattern)) {
                return modules;
            }
        }

        return [];
    }

    /**
     * 动态加载模块
     */
    async loadModule(moduleName) {
        if (this.modules.has(moduleName)) {
            return this.modules.get(moduleName);
        }

        const module = await this.importModule(moduleName);
        this.modules.set(moduleName, module);
        
        if (module && typeof module.init === 'function') {
            await module.init();
        }

        return module;
    }

    /**
     * 导入模块
     */
    async importModule(moduleName) {
        // 可用的模块映射
        const availableModules = {
            'dashboard': './modules/dashboard.js',
            'charts': './modules/charts.js'
            // 其他模块可以根据需要添加
        };

        if (!availableModules[moduleName]) {
            console.warn(`模块 ${moduleName} 不可用`);
            return null;
        }

        try {
            return await import(availableModules[moduleName]);
        } catch (error) {
            console.warn(`模块 ${moduleName} 加载失败:`, error);
            return null;
        }
    }

    /**
     * 设置错误处理
     */
    setupErrorHandling() {
        // 全局错误捕获
        window.addEventListener('error', (event) => {
            console.error('全局错误:', event.error);
            this.showError('发生了一个错误，部分功能可能无法正常使用');
        });

        // Promise 错误捕获
        window.addEventListener('unhandledrejection', (event) => {
            console.error('未处理的 Promise 错误:', event.reason);
            this.showError('操作失败，请重试');
        });

        // AJAX 错误处理
        this.setupAjaxErrorHandling();
    }

    /**
     * 设置 AJAX 错误处理
     */
    setupAjaxErrorHandling() {
        // 拦截 fetch 请求
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch(...args);
                
                if (!response.ok) {
                    this.handleHttpError(response);
                }
                
                return response;
            } catch (error) {
                this.handleNetworkError(error);
                throw error;
            }
        };
    }

    /**
     * 处理 HTTP 错误
     */
    handleHttpError(response) {
        switch (response.status) {
            case 401:
                this.showError('登录已过期，请重新登录');
                setTimeout(() => {
                    window.location.href = '/admin/login';
                }, 2000);
                break;
            case 403:
                this.showError('没有权限执行此操作');
                break;
            case 404:
                this.showError('请求的资源不存在');
                break;
            case 500:
                this.showError('服务器内部错误，请稍后重试');
                break;
            default:
                this.showError(`请求失败 (${response.status})`);
        }
    }

    /**
     * 处理网络错误
     */
    handleNetworkError(error) {
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            this.showError('网络连接失败，请检查网络连接');
        } else {
            this.showError('网络请求失败，请重试');
        }
    }

    /**
     * 显示错误消息
     */
    showError(message) {
        if (this.messages) {
            this.messages.show(message, 'error');
        } else {
            alert(message);
        }
    }

    /**
     * 显示成功消息
     */
    showSuccess(message) {
        if (this.messages) {
            this.messages.show(message, 'success');
        }
    }

    /**
     * 显示警告消息
     */
    showWarning(message) {
        if (this.messages) {
            this.messages.show(message, 'warning');
        }
    }

    /**
     * 显示信息消息
     */
    showInfo(message) {
        if (this.messages) {
            this.messages.show(message, 'info');
        }
    }

    /**
     * 重载模块
     */
    async reloadModule(moduleName) {
        this.modules.delete(moduleName);
        return await this.loadModule(moduleName);
    }

    /**
     * 获取模块
     */
    getModule(moduleName) {
        return this.modules.get(moduleName);
    }

    /**
     * 销毁应用
     */
    destroy() {
        // 清理模块
        for (const [name, module] of this.modules) {
            if (module && typeof module.destroy === 'function') {
                module.destroy();
            }
        }
        this.modules.clear();

        // 清理全局对象
        delete window.AdminApp;
        delete window.showMessage;

        this.initialized = false;
        console.log('Admin 应用已销毁');
    }
}

// 创建并初始化应用实例
const app = new AdminApp();

// DOM 加载完成后初始化
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => app.init());
} else {
    app.init();
}

// 导出应用实例
export default app; 