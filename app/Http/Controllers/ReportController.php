<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;

class ReportController extends Controller
{
    private $markdownConverter;
    
    public function __construct()
    {
        // 配置 Markdown 转换器
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 5,
        ];
        
        $this->markdownConverter = new CommonMarkConverter($config);
    }
    
    /**
     * 显示报告列表
     */
    public function index()
    {
        $reportsDir = storage_path('reports');
        
        if (!File::exists($reportsDir)) {
            File::makeDirectory($reportsDir, 0755, true);
        }
        
        $reports = collect(File::glob($reportsDir . '/*.md'))
            ->map(function ($file) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $content = File::get($file);
                
                // 提取标题（第一个 # 标题或文件名）
                $title = $filename;
                if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                    $title = trim($matches[1]);
                }
                
                return [
                    'slug' => $filename,
                    'title' => $title,
                    'mtime' => File::lastModified($file),
                    'size' => File::size($file),
                ];
            })
            ->sortByDesc('mtime')
            ->values();
        
        return view('index', compact('reports'));
    }
    
    /**
     * 显示单个报告
     */
    public function show($slug)
    {
        $filePath = storage_path("reports/{$slug}.md");
        
        if (!File::exists($filePath)) {
            abort(404, '报告不存在');
        }
        
        // 使用缓存提高性能
        $cacheKey = "report.{$slug}." . File::lastModified($filePath);
        
        $data = Cache::remember($cacheKey, 3600, function () use ($filePath, $slug) {
            $content = File::get($filePath);
            
            // 转换 Markdown 为 HTML
            $html = $this->markdownConverter->convert($content);
            
            // 提取标题
            $title = $slug;
            if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                $title = trim($matches[1]);
            }
            
            return [
                'title' => $title,
                'html' => $html,
                'slug' => $slug,
                'mtime' => File::lastModified($filePath),
                'size' => File::size($filePath),
            ];
        });
        
        return view('report', $data);
    }
    
    /**
     * 清除报告缓存
     */
    public function clearCache($slug = null)
    {
        if ($slug) {
            $pattern = "report.{$slug}.*";
        } else {
            $pattern = "report.*";
        }
        
        // 这里应该实现缓存清除逻辑
        Cache::flush();
        
        return response()->json(['message' => '缓存已清除']);
    }
} 