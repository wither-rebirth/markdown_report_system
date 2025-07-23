<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- 管理端样式 -->
    @vite(['resources/css/admin.new.css'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('styles')
</head>
<body class="admin-body">
    <!-- 移动端遮罩层 -->
    <div class="mobile-overlay" id="mobileOverlay"></div>
    
    <div class="admin-container">
        <!-- 侧边栏 -->
        <nav class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h3>
                        <i class="fas fa-shield-alt"></i> 
                        <span>Admin Panel</span>
                    </h3>
                    <button class="sidebar-close d-lg-none" id="sidebarClose">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="sidebar-user">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-role">Administrator</div>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-content">
                <ul class="sidebar-menu">
                    <li class="menu-section">
                        <span class="menu-section-title">Main Features</span>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.blog.index') }}" class="menu-link {{ request()->routeIs('admin.blog*') ? 'active' : '' }}">
                            <i class="fas fa-edit"></i>
                            <span class="menu-text">Blog Management</span>
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.categories.index') }}" class="menu-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                            <i class="fas fa-folder-open"></i>
                            <span class="menu-text">Categories</span>
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.tags.index') }}" class="menu-link {{ request()->routeIs('admin.tags*') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i>
                            <span class="menu-text">Tags</span>
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.comments.index') }}" class="menu-link {{ request()->routeIs('admin.comments*') ? 'active' : '' }}">
                            <i class="fas fa-comments"></i>
                            <span class="menu-text">Comments</span>
                            @if(isset($pendingCommentsCount) && $pendingCommentsCount > 0)
                                <span class="menu-badge">{{ $pendingCommentsCount }}</span>
                            @endif
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.report-locks.index') }}" class="menu-link {{ request()->routeIs('admin.report-locks*') ? 'active' : '' }}">
                            <i class="fas fa-lock"></i>
                            <span class="menu-text">Report Locks</span>
                        </a>
                    </li>
                    
                    <li class="menu-divider"></li>
                    
                    <li class="menu-section">
                        <span class="menu-section-title">Analytics</span>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.analytics.index') }}" class="menu-link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span class="menu-text">Statistics</span>
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.backup.index') }}" class="menu-link {{ request()->routeIs('admin.backup*') ? 'active' : '' }}">
                            <i class="fas fa-database"></i>
                            <span class="menu-text">System Backup</span>
                        </a>
                    </li>
                    
                    <li class="menu-divider"></li>
                    
                    <li class="menu-section">
                        <span class="menu-section-title">Quick Links</span>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('home.index') }}" class="menu-link" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                            <span class="menu-text">Visit Site</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="sidebar-footer">
                <div class="system-status">
                    <div class="status-item">
                        <i class="fas fa-circle text-success"></i>
                        <span>System Running</span>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- 主内容区域 -->
        <div class="admin-main">
            <!-- 顶部导航栏 -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="page-breadcrumb">
                        <div class="page-title-container">
                            @php
                                $pageTitle = View::yieldContent('page-title') ?: 'Admin Dashboard';
                                $pageIcon = '';
                                switch($pageTitle) {
                                    case 'Dashboard':
                                        $pageIcon = 'fas fa-tachometer-alt';
                                        break;
                                    case 'Blog Management':
                                        $pageIcon = 'fas fa-edit';
                                        break;
                                    case 'Categories':
                                        $pageIcon = 'fas fa-folder-open';
                                        break;
                                    case 'Tags':
                                        $pageIcon = 'fas fa-tags';
                                        break;
                                    case 'Comments':
                                        $pageIcon = 'fas fa-comments';
                                        break;
                                    case 'Report Lock Management':
                                        $pageIcon = 'fas fa-lock';
                                        break;
                                    case 'Statistics':
                                        $pageIcon = 'fas fa-chart-line';
                                        break;
                                    case 'Backup Management':
                                        $pageIcon = 'fas fa-database';
                                        break;
                                    case 'Create New Post':
                                        $pageIcon = 'fas fa-pen';
                                        break;
                                    case 'Edit Post':
                                        $pageIcon = 'fas fa-edit';
                                        break;
                                    case 'Create Category':
                                        $pageIcon = 'fas fa-folder-plus';
                                        break;
                                    case 'Edit Category':
                                        $pageIcon = 'fas fa-folder-open';
                                        break;
                                    case 'Create Tag':
                                        $pageIcon = 'fas fa-tag';
                                        break;
                                    case 'Edit Tag':
                                        $pageIcon = 'fas fa-tag';
                                        break;
                                    default:
                                        $pageIcon = 'fas fa-cog';
                                }
                            @endphp
                            <h1 class="page-title">
                                @if($pageIcon)
                                    <i class="{{ $pageIcon }}"></i>
                                @endif
                                <span>{{ $pageTitle }}</span>
                            </h1>
                        </div>
                        @hasSection('breadcrumb')
                            <nav class="breadcrumb-nav">
                                @yield('breadcrumb')
                            </nav>
                        @endif
                    </div>
                </div>
                
                <div class="header-right">
                    <!-- 快速操作 -->
                    <div class="quick-actions d-none d-md-flex">
                        <a href="{{ route('admin.blog.create') }}" class="quick-action-btn" title="Write Post">
                            <i class="fas fa-plus"></i>
                        </a>
                        <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}" class="quick-action-btn" title="Pending Comments">
                            <i class="fas fa-bell"></i>
                            @if(isset($pendingCommentsCount) && $pendingCommentsCount > 0)
                                <span class="action-badge">{{ $pendingCommentsCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.analytics.index') }}" class="quick-action-btn" title="Analytics">
                            <i class="fas fa-chart-bar"></i>
                        </a>
                    </div>
                    
                    <!-- 用户菜单 -->
                    <div class="user-menu">
                        <div class="user-dropdown">
                            <button class="user-dropdown-toggle" id="userDropdownToggle">
                                <div class="user-avatar-sm">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="user-info-sm d-none d-md-block">
                                    <span class="username">{{ Auth::user()->name }}</span>
                                    <small class="user-status">Online</small>
                                </div>
                                <i class="fas fa-chevron-down dropdown-arrow"></i>
                            </button>
                            
                            <div class="user-dropdown-menu" id="userDropdownMenu">
                                <div class="dropdown-header">
                                    <div class="user-info">
                                        <div class="user-name">{{ Auth::user()->name }}</div>
                                        <div class="user-email">{{ Auth::user()->email ?? 'Administrator Account' }}</div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                                <a href="{{ route('home.index') }}" class="dropdown-item" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span>Visit Site</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('admin.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- 内容区域 -->
            <main class="admin-content">
                <!-- 消息提示 -->
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Success</div>
                            <div class="alert-message">{{ session('success') }}</div>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-error" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Error</div>
                            <div class="alert-message">{{ session('error') }}</div>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-error" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Validation Error</div>
                            <div class="alert-message">
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- 管理端脚本 -->
    @vite(['resources/js/admin.new.js'])
    
    @stack('scripts')
</body>
</html> 