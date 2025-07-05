<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? "wither's blog" }}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="description" content="wither's blog - 个人技术博客与靶场报告展示系统">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- 预加载关键资源 -->
    <link rel="preload" href="{{ asset('css/app.css') }}" as="style">
    <link rel="preload" href="{{ asset('js/app.js') }}" as="script">
    
    <!-- 引入样式文件 -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/layout.css', 'resources/js/layout.js'])
    
    @stack('styles')
</head>
<body>
    <!-- 动态背景效果 -->
    <div id="background-effects">
        <!-- 星空背景 (暗黑模式) -->
        <div id="stars-background"></div>
        <!-- 日间模式背景 -->
        <div id="daylight-background"></div>
    </div>
    
    <div id="app">
        <!-- 增强的导航栏 -->
        <header class="navbar">
            <div class="navbar-container">
                <!-- 左侧：系统标题 -->
                <div class="navbar-left">
                    <h1 class="navbar-title">
                        <a href="{{ url('/') }}">🌟 wither's blog</a>
                    </h1>
                </div>
                
                <!-- 右侧：导航链接和操作按钮 -->
                <nav class="navbar-right">
                    <!-- 导航菜单 -->
                    <div class="navbar-menu">
                        <a href="{{ route('blog.index') }}" class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                            </svg>
                            博客
                        </a>
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                            </svg>
                            靶场报告
                        </a>
                    </div>
                    
                    <!-- 操作按钮 -->
                    <button onclick="toggleMobileMenu()" class="nav-btn mobile-menu-btn" title="菜单">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3,6H21V8H3V6M3,11H21V13H3V11M3,16H21V18H3V16Z"/>
                        </svg>
                    </button>
                    <button onclick="toggleFullscreen()" class="nav-btn" title="全屏切换">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
                        </svg>
                    </button>
                    <button onclick="toggleDarkMode()" class="nav-btn" title="切换主题">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.75,4.09L15.22,6.03L16.13,9.09L13.5,7.28L10.87,9.09L11.78,6.03L9.25,4.09L12.44,4L13.5,1L14.56,4L17.75,4.09M21.25,11L19.61,12.25L20.2,14.23L18.5,13.06L16.8,14.23L17.39,12.25L15.75,11L17.81,10.95L18.5,9L19.19,10.95L21.25,11M18.97,15.95C19.8,15.87 20.69,17.05 20.16,17.8C19.84,18.25 19.5,18.67 19.08,19.07C15.17,23 8.84,23 4.94,19.07C1.03,15.17 1.03,8.83 4.94,4.93C5.34,4.53 5.76,4.17 6.21,3.85C6.96,3.32 8.14,4.21 8.06,5.04C7.79,7.9 8.75,10.87 10.95,13.06C13.14,15.26 16.1,16.22 18.97,15.95M17.33,17.97C14.5,17.81 11.7,16.64 9.53,14.5C7.36,12.31 6.2,9.5 6.04,6.68C3.23,9.82 3.34,14.4 6.35,17.41C9.37,20.43 14,20.54 17.33,17.97Z"/>
                        </svg>
                    </button>
                </nav>
                
                <!-- 移动端导航菜单 -->
                <div id="mobile-menu" class="mobile-menu">
                    <div class="mobile-menu-content">
                        <a href="{{ route('blog.index') }}" class="mobile-nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                            </svg>
                            博客
                        </a>
                        <a href="{{ route('reports.index') }}" class="mobile-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                            </svg>
                            靶场报告
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- 移动端菜单遮罩 -->
        <div id="mobile-menu-overlay" class="mobile-menu-overlay" onclick="toggleMobileMenu()"></div>

        <!-- 主要内容 -->
        <main>
            @yield('content')
        </main>

        <!-- 页脚 -->
        <footer>
            <div class="container">
                <p>
                    © {{ date('Y') }} wither's blog | 
                    <a href="https://github.com" target="_blank" rel="noopener">GitHub</a>
                </p>
            </div>
        </footer>

        <!-- 回到顶部按钮 -->
        <button 
            id="scroll-top-btn" 
            class="scroll-top"
            title="回到顶部"
            style="display: none;"
            onclick="scrollToTop()"
        >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/>
            </svg>
        </button>

        <!-- 加载指示器 -->
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading"></div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
