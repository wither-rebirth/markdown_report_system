<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\BlogComment;
use App\Models\Category;
use App\Models\Tag;
use App\Models\ReportLock;

class DashboardController extends Controller
{
    /**
     * 显示管理端仪表板
     */
    public function index()
    {
        // 获取博客文章统计
        $blogStats = $this->getBlogStats();
        
        // 获取评论统计
        $commentStats = $this->getCommentStats();
        
        // 获取分类和标签统计
        $categoryCount = Category::count();
        $tagCount = Tag::count();
        
        // 获取report锁定统计
        $reportLockStats = $this->getReportLockStats();
        
        // 获取分析统计
        $analyticsStats = $this->getAnalyticsStats();
        
        // 获取最新评论
        $latestComments = BlogComment::latest()
            ->take(5)
            ->get();
        
        return view('admin.dashboard', compact(
            'blogStats',
            'commentStats',
            'categoryCount',
            'tagCount',
            'reportLockStats',
            'analyticsStats',
            'latestComments'
        ));
    }

    /**
     * 获取博客文章统计
     */
    private function getBlogStats()
    {
        $blogDir = storage_path('blog');
        
        if (!File::exists($blogDir)) {
            return [
                'total' => 0,
                'published' => 0,
                'draft' => 0,
            ];
        }

        $total = 0;
        $published = 0;
        $draft = 0;

        // 统计独立的 .md 文件
        $mdFiles = File::glob($blogDir . '/*.md');
        $total += count($mdFiles);
        
        // 这里可以根据文件内容判断是否为草稿
        $published += count($mdFiles); // 暂时假设所有文件都是已发布的

        // 统计文件夹类型的博客
        $directories = File::directories($blogDir);
        foreach ($directories as $dir) {
            $indexFile = $dir . '/index.md';
            if (File::exists($indexFile)) {
                $total++;
                $published++; // 暂时假设所有文件都是已发布的
            }
        }

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
        ];
    }

    /**
     * 获取评论统计
     */
    private function getCommentStats()
    {
        return [
            'total' => BlogComment::count(),
            'approved' => BlogComment::where('is_approved', true)->count(),
            'pending' => BlogComment::where('is_approved', false)->count(),
            'today' => BlogComment::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * 获取report锁定统计
     */
    private function getReportLockStats()
    {
        return [
            'total' => ReportLock::count(),
            'enabled' => ReportLock::where('is_enabled', true)->count(),
            'disabled' => ReportLock::where('is_enabled', false)->count(),
        ];
    }

    /**
     * 获取分析统计
     */
    private function getAnalyticsStats()
    {
        // 这里可以添加真实的分析数据，暂时使用假数据
        return [
            'pageviews' => 1234,
            'visitors' => 456,
        ];
    }
}
