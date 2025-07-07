<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AboutMeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 首页 - 主页
Route::get('/', [HomeController::class, 'index'])->name('home.index');

// 博客路由
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');

// 博客文章详情
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// 博客评论相关路由
Route::post('/blog/{slug}/comments', [BlogController::class, 'storeComment'])->name('blog.comments.store');
Route::get('/blog/{slug}/comments', [BlogController::class, 'getComments'])->name('blog.comments.get');

// 博客图片访问
Route::get('/blog-images/{folder}/{filename}', [BlogController::class, 'getBlogImage'])
    ->name('blog.image')
    ->where(['folder' => '[a-zA-Z0-9\-_]+', 'filename' => '.+']);

// 关于我页面
Route::get('/aboutme', [AboutMeController::class, 'index'])->name('aboutme.index');

// 靶场报告路由组
Route::prefix('reports')->group(function () {
    // 报告列表页面
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    

    
    // 显示单个报告
    Route::get('/{slug}', [ReportController::class, 'show'])->name('reports.show');
    
    // 删除报告
    Route::delete('/{slug}', [ReportController::class, 'destroy'])->name('reports.destroy');
    
    // 批量删除报告
    Route::post('/batch-delete', [ReportController::class, 'destroyMultiple'])->name('reports.batch-delete');
    
    // 清除缓存
    Route::post('/clear-cache/{slug?}', [ReportController::class, 'clearCache'])->name('reports.clear-cache');
    
    // 获取统计信息
    Route::get('/api/stats', [ReportController::class, 'stats'])->name('reports.stats');
});

// Hackthebox 报告图片访问
Route::get('/htb-images/{folder}/{filename}', [ReportController::class, 'getHacktheboxImage'])
    ->name('reports.htb-image')
    ->where(['folder' => '[a-zA-Z0-9\-_]+', 'filename' => '.+']);

// 兼容性路由 - 直接访问HTML文件
Route::get('/{slug}.html', function ($slug) {
    return redirect()->route('reports.show', $slug);
})->where('slug', '[a-zA-Z0-9\-_]+');

// 管理端路由
Route::prefix('admin')->name('admin.')->group(function () {
    // 认证路由（不需要登录即可访问）
    Route::get('/login', [App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login']);
    Route::get('/setup', [App\Http\Controllers\Admin\AuthController::class, 'showSetup'])->name('setup');
    Route::post('/setup', [App\Http\Controllers\Admin\AuthController::class, 'setup']);
    
    // 需要认证的管理端路由
    Route::middleware('admin')->group(function () {
        // 仪表板
        Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard.index');
        
        // 认证相关
        Route::post('/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
        
        // 博客管理
        Route::resource('blog', App\Http\Controllers\Admin\BlogController::class);
        
        // 分类管理
        Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
        Route::post('categories/{category}/toggle-status', [App\Http\Controllers\Admin\CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::post('categories/update-order', [App\Http\Controllers\Admin\CategoryController::class, 'updateOrder'])->name('categories.update-order');
        
        // 标签管理
        Route::resource('tags', App\Http\Controllers\Admin\TagController::class);
        Route::post('tags/{tag}/toggle-status', [App\Http\Controllers\Admin\TagController::class, 'toggleStatus'])->name('tags.toggle-status');
        Route::post('tags/bulk-delete', [App\Http\Controllers\Admin\TagController::class, 'bulkDelete'])->name('tags.bulk-delete');
        
        // 评论管理
        Route::resource('comments', App\Http\Controllers\Admin\CommentController::class)->except(['create', 'store']);
        Route::post('comments/bulk-action', [App\Http\Controllers\Admin\CommentController::class, 'bulkAction'])->name('comments.bulk-action');
        Route::post('comments/{comment}/toggle-approval', [App\Http\Controllers\Admin\CommentController::class, 'toggleApproval'])->name('comments.toggle-approval');
        Route::get('comments/blog/{slug}', [App\Http\Controllers\Admin\CommentController::class, 'byBlog'])->name('comments.by-blog');
        Route::post('comments/detect-spam', [App\Http\Controllers\Admin\CommentController::class, 'detectSpam'])->name('comments.detect-spam');
    });
});
