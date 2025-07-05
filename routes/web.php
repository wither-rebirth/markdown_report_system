<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BlogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 首页 - 博客系统
Route::get('/', [BlogController::class, 'index'])->name('blog.index');

// 博客文章详情
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// 博客图片访问
Route::get('/blog-images/{folder}/{filename}', [BlogController::class, 'getBlogImage'])
    ->name('blog.image')
    ->where(['folder' => '[a-zA-Z0-9\-_]+', 'filename' => '.+']);

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
