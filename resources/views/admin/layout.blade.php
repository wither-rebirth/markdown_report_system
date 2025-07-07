<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '管理后台') - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- 管理端样式 -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
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
                        <span>管理中心</span>
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
                        <div class="user-role">管理员</div>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-content">
                <ul class="sidebar-menu">
                    <li class="menu-section">
                        <span class="menu-section-title">主要功能</span>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="menu-text">仪表板</span>
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.blog.index') }}" class="menu-link {{ request()->routeIs('admin.blog*') ? 'active' : '' }}">
                            <i class="fas fa-edit"></i>
                            <span class="menu-text">博客管理</span>
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.categories.index') }}" class="menu-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                            <i class="fas fa-folder-open"></i>
                            <span class="menu-text">分类管理</span>
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.tags.index') }}" class="menu-link {{ request()->routeIs('admin.tags*') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i>
                            <span class="menu-text">标签管理</span>
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.comments.index') }}" class="menu-link {{ request()->routeIs('admin.comments*') ? 'active' : '' }}">
                            <i class="fas fa-comments"></i>
                            <span class="menu-text">评论管理</span>
                            @if(isset($pendingCommentsCount) && $pendingCommentsCount > 0)
                                <span class="menu-badge">{{ $pendingCommentsCount }}</span>
                            @endif
                        </a>
                    </li>
                    
                    <li class="menu-divider"></li>
                    
                    <li class="menu-section">
                        <span class="menu-section-title">数据分析</span>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.analytics.index') }}" class="menu-link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span class="menu-text">数据统计</span>
                        </a>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('admin.backup.index') }}" class="menu-link {{ request()->routeIs('admin.backup*') ? 'active' : '' }}">
                            <i class="fas fa-database"></i>
                            <span class="menu-text">系统备份</span>
                        </a>
                    </li>
                    
                    <li class="menu-divider"></li>
                    
                    <li class="menu-section">
                        <span class="menu-section-title">快速链接</span>
                    </li>
                    
                    <li class="menu-item">
                        <a href="{{ route('home.index') }}" class="menu-link" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                            <span class="menu-text">前台首页</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="sidebar-footer">
                <div class="system-status">
                    <div class="status-item">
                        <i class="fas fa-circle text-success"></i>
                        <span>系统运行正常</span>
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
                                $pageTitle = View::yieldContent('page-title') ?: '管理后台';
                                $pageIcon = '';
                                switch($pageTitle) {
                                    case '仪表板':
                                        $pageIcon = 'fas fa-tachometer-alt';
                                        break;
                                    case '博客管理':
                                        $pageIcon = 'fas fa-edit';
                                        break;
                                    case '分类管理':
                                        $pageIcon = 'fas fa-folder-open';
                                        break;
                                    case '标签管理':
                                        $pageIcon = 'fas fa-tags';
                                        break;
                                    case '评论管理':
                                        $pageIcon = 'fas fa-comments';
                                        break;
                                    case '数据分析':
                                        $pageIcon = 'fas fa-chart-line';
                                        break;
                                    case '备份管理':
                                        $pageIcon = 'fas fa-database';
                                        break;
                                    case '写新文章':
                                        $pageIcon = 'fas fa-pen';
                                        break;
                                    case '编辑文章':
                                        $pageIcon = 'fas fa-edit';
                                        break;
                                    case '新建分类':
                                        $pageIcon = 'fas fa-folder-plus';
                                        break;
                                    case '编辑分类':
                                        $pageIcon = 'fas fa-folder-open';
                                        break;
                                    case '新建标签':
                                        $pageIcon = 'fas fa-tag';
                                        break;
                                    case '编辑标签':
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
                        <a href="{{ route('admin.blog.create') }}" class="quick-action-btn" title="写文章">
                            <i class="fas fa-plus"></i>
                        </a>
                        <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}" class="quick-action-btn" title="待审评论">
                            <i class="fas fa-bell"></i>
                            @if(isset($pendingCommentsCount) && $pendingCommentsCount > 0)
                                <span class="action-badge">{{ $pendingCommentsCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.analytics.index') }}" class="quick-action-btn" title="数据分析">
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
                                    <small class="user-status">在线</small>
                                </div>
                                <i class="fas fa-chevron-down dropdown-arrow"></i>
                            </button>
                            
                            <div class="user-dropdown-menu" id="userDropdownMenu">
                                <div class="dropdown-header">
                                    <div class="user-info">
                                        <div class="user-name">{{ Auth::user()->name }}</div>
                                        <div class="user-email">{{ Auth::user()->email ?? '管理员账户' }}</div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>仪表板</span>
                                </a>
                                <a href="{{ route('home.index') }}" class="dropdown-item" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span>访问网站</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('admin.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>退出登录</span>
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
                            <div class="alert-title">操作成功</div>
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
                            <div class="alert-title">操作失败</div>
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
                            <div class="alert-title">表单验证错误</div>
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
    <script src="{{ asset('js/admin.js') }}"></script>
    
    <!-- 内联脚本 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 侧边栏切换
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            function toggleSidebar() {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-open');
            }
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }
            
            if (sidebarClose) {
                sidebarClose.addEventListener('click', toggleSidebar);
            }
            
            if (overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }
            
            // 用户下拉菜单
            const userDropdownToggle = document.getElementById('userDropdownToggle');
            const userDropdownMenu = document.getElementById('userDropdownMenu');
            
            if (userDropdownToggle && userDropdownMenu) {
                userDropdownToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdownMenu.classList.toggle('active');
                });
                
                // 点击其他地方关闭下拉菜单
                document.addEventListener('click', function() {
                    userDropdownMenu.classList.remove('active');
                });
            }
            
            // 表格确认删除
            document.querySelectorAll('[data-confirm]').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    if (!confirm(this.dataset.confirm)) {
                        e.preventDefault();
                    }
                });
            });
            
            // 自动隐藏消息提示
            setTimeout(function() {
                document.querySelectorAll('.alert').forEach(function(alert) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                });
            }, 5000);
            
            // 添加页面加载完成的视觉效果
            document.body.classList.add('loaded');
        });
        
        // 显示消息提示函数
        function showMessage(message, type = 'success') {
            const alertTypes = {
                success: { icon: 'fas fa-check-circle', title: '操作成功' },
                error: { icon: 'fas fa-exclamation-triangle', title: '操作失败' },
                warning: { icon: 'fas fa-exclamation-circle', title: '警告' },
                info: { icon: 'fas fa-info-circle', title: '提示' }
            };
            
            const alertInfo = alertTypes[type] || alertTypes.info;
            
            const alertHtml = `
                <div class="alert alert-${type}" role="alert">
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
                </div>
            `;
            
            const content = document.querySelector('.admin-content');
            if (content) {
                content.insertAdjacentHTML('afterbegin', alertHtml);
            }
        }
    </script>
    
    @stack('scripts')
</body>
</html> 