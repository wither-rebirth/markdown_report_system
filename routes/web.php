<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 首页 - 显示报告列表
Route::get('/', [ReportController::class, 'index'])->name('reports.index');

// 上传页面
Route::get('/upload', [ReportController::class, 'create'])->name('reports.create');

// 处理文件上传
Route::post('/upload', [ReportController::class, 'store'])->name('reports.store');

// 显示单个报告
Route::get('/reports/{slug}', [ReportController::class, 'show'])->name('reports.show');

// Hackthebox 报告图片访问
Route::get('/htb-images/{folder}/{filename}', [ReportController::class, 'getHacktheboxImage'])
    ->name('reports.htb-image')
    ->where(['folder' => '[a-zA-Z0-9\-_]+', 'filename' => '.+']);

// 删除报告
Route::delete('/reports/{slug}', [ReportController::class, 'destroy'])->name('reports.destroy');

// 批量删除报告
Route::post('/reports/batch-delete', [ReportController::class, 'destroyMultiple'])->name('reports.batch-delete');

// 清除缓存
Route::post('/reports/clear-cache/{slug?}', [ReportController::class, 'clearCache'])->name('reports.clear-cache');

// 获取统计信息
Route::get('/api/stats', [ReportController::class, 'stats'])->name('reports.stats');

// 兼容性路由 - 直接访问HTML文件
Route::get('/{slug}.html', function ($slug) {
    return redirect()->route('reports.show', $slug);
})->where('slug', '[a-zA-Z0-9\-_]+');
