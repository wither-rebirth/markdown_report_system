import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // 前台资源
                'resources/css/app.css',
                'resources/css/layout.css',
                'resources/css/index.css',
                'resources/css/report.css',
                'resources/css/report-password.css',
                'resources/css/blog.css',
                'resources/css/aboutme.css',
                'resources/css/home.css',
                'resources/js/app.js',
                'resources/js/layout.js',
                'resources/js/index.js',
                'resources/js/report.js',
                'resources/js/report-password.js',
                'resources/js/home.js',
                
                // 管理后台基础资源
                'resources/css/admin.new.css',
                'resources/js/admin.new.js',
                'resources/js/admin-layout.js',
                
                            // 管理后台模块化资源
            'resources/css/admin/dashboard.css',
            'resources/css/admin/auth.css',
            'resources/css/admin/blog.css',
            'resources/css/admin/analytics.css',
            'resources/css/admin/backup.css',
            'resources/css/admin/categories.css',
            'resources/css/admin/tags.css',
            'resources/css/admin/comments.css',
            'resources/css/admin/report-locks.css',
            'resources/js/admin/blog.js',
            'resources/js/admin/analytics.js',
            'resources/js/admin/backup.js',
            'resources/js/admin/categories.js',
            'resources/js/admin/tags.js',
            'resources/js/admin/comments.js',
            'resources/js/admin/report-locks.js',
                'resources/js/modules/dashboard.js',
                'resources/js/modules/charts.js'
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
        },
    },
}); 