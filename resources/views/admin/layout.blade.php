<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '管理后台') - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- 管理端样式 -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-container">
        <!-- 侧边栏 -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-cog"></i> 管理后台</h3>
            </div>
            
            <ul class="sidebar-menu">
                <li class="menu-item">
                    <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>仪表板</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="{{ route('admin.blog.index') }}" class="menu-link {{ request()->routeIs('admin.blog*') ? 'active' : '' }}">
                        <i class="fas fa-blog"></i>
                        <span>博客管理</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="{{ route('admin.categories.index') }}" class="menu-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                        <i class="fas fa-folder"></i>
                        <span>分类管理</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="{{ route('admin.tags.index') }}" class="menu-link {{ request()->routeIs('admin.tags*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i>
                        <span>标签管理</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="{{ route('admin.comments.index') }}" class="menu-link {{ request()->routeIs('admin.comments*') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i>
                        <span>评论管理</span>
                    </a>
                </li>
                
                <li class="menu-divider"></li>
                
                <li class="menu-item">
                    <a href="{{ route('admin.analytics.index') }}" class="menu-link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>数据分析</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="{{ route('admin.backup.index') }}" class="menu-link {{ request()->routeIs('admin.backup*') ? 'active' : '' }}">
                        <i class="fas fa-shield-alt"></i>
                        <span>备份管理</span>
                    </a>
                </li>
                
                <li class="menu-divider"></li>
                
                <li class="menu-item">
                    <a href="{{ route('home.index') }}" class="menu-link" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>前台首页</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- 主内容区域 -->
        <div class="admin-main">
            <!-- 顶部导航栏 -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">@yield('page-title', '管理后台')</h1>
                </div>
                
                <div class="header-right">
                    <div class="user-menu">
                        <span class="username">{{ Auth::user()->name }}</span>
                        <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="logout-btn">
                                <i class="fas fa-sign-out-alt"></i> 退出
                            </button>
                        </form>
                    </div>
                </div>
            </header>
            
            <!-- 内容区域 -->
            <main class="admin-content">
                <!-- 消息提示 -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- 管理端脚本 -->
    <script src="{{ asset('js/admin.js') }}"></script>
    @stack('scripts')
</body>
</html> 