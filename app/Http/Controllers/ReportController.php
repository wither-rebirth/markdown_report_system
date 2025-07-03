<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
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
                
                // 提取摘要（第一段文字或前100个字符）
                $excerpt = $this->extractExcerpt($content);
                
                return [
                    'slug' => $filename,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'mtime' => File::lastModified($file),
                    'size' => File::size($file),
                    'status' => 'active'
                ];
            })
            ->sortByDesc('mtime')
            ->values();
        
        return view('index', compact('reports'));
    }
    
    /**
     * 显示上传表单
     */
    public function create()
    {
        return view('upload');
    }
    
    /**
     * 处理文件上传
     */
    public function store(Request $request)
    {
        try {
            // 验证上传的文件
            $validator = Validator::make($request->all(), [
                'markdown_file' => 'required|file|mimes:md,txt|max:10240', // 最大10MB
                'title' => 'nullable|string|max:255',
                'slug' => 'nullable|string|max:255|regex:/^[a-z0-9-_]+$/i',
                'overwrite' => 'nullable|boolean'
            ], [
                'markdown_file.required' => '请选择要上传的文件',
                'markdown_file.mimes' => '只允许上传 .md 或 .txt 文件',
                'markdown_file.max' => '文件大小不能超过 10MB',
                'slug.regex' => '文件名只能包含字母、数字、横杠和下划线'
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $file = $request->file('markdown_file');
            $content = $file->get();
            
            // 验证文件内容
            if (empty(trim($content))) {
                return back()->withErrors(['markdown_file' => '文件内容不能为空'])->withInput();
            }
            
            // 生成文件名
            $slug = $request->input('slug');
            if (empty($slug)) {
                $slug = $this->generateSlugFromContent($content, $file->getClientOriginalName());
            }
            
            // 确保文件名唯一
            $originalSlug = $slug;
            $counter = 1;
            while (File::exists(storage_path("reports/{$slug}.md")) && !$request->input('overwrite')) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            // 处理标题
            $title = $request->input('title');
            if (!empty($title)) {
                // 如果提供了标题，确保内容以标题开头
                if (!preg_match('/^#\s+/', $content)) {
                    $content = "# {$title}\n\n" . $content;
                } else {
                    // 替换现有标题
                    $content = preg_replace('/^#\s+.+$/m', "# {$title}", $content, 1);
                }
            }
            
            // 添加元数据注释
            $metadata = "<!-- \n";
            $metadata .= "上传时间: " . now()->format('Y-m-d H:i:s') . "\n";
            $metadata .= "原文件名: " . $file->getClientOriginalName() . "\n";
            $metadata .= "文件大小: " . $this->formatFileSize($file->getSize()) . "\n";
            $metadata .= "-->\n\n";
            
            $content = $metadata . $content;
            
            // 保存文件
            $filePath = storage_path("reports/{$slug}.md");
            File::put($filePath, $content);
            
            // 清除相关缓存
            $this->clearReportCache($slug);
            
            // 生成HTML预览
            $this->generateHtmlPreview($slug, $content);
            
            return redirect()->route('reports.show', $slug)
                ->with('success', "报告 '{$slug}' 上传成功！");
                
        } catch (\Exception $e) {
            Log::error('文件上传失败: ' . $e->getMessage());
            return back()->withErrors(['upload' => '文件上传失败: ' . $e->getMessage()])->withInput();
        }
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
            
            // 移除元数据注释
            $content = preg_replace('/<!--.*?-->/s', '', $content);
            
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
     * 删除报告
     */
    public function destroy($slug)
    {
        try {
            $filePath = storage_path("reports/{$slug}.md");
            $htmlPath = public_path("{$slug}.html");
            
            if (!File::exists($filePath)) {
                return response()->json(['error' => '报告不存在'], 404);
            }
            
            // 删除markdown文件
            File::delete($filePath);
            
            // 删除HTML文件（如果存在）
            if (File::exists($htmlPath)) {
                File::delete($htmlPath);
            }
            
            // 清除缓存
            $this->clearReportCache($slug);
            
            return response()->json(['message' => '报告删除成功']);
            
        } catch (\Exception $e) {
            Log::error('删除报告失败: ' . $e->getMessage());
            return response()->json(['error' => '删除失败'], 500);
        }
    }
    
    /**
     * 批量删除报告
     */
    public function destroyMultiple(Request $request)
    {
        $slugs = $request->input('slugs', []);
        $deleted = [];
        $errors = [];
        
        foreach ($slugs as $slug) {
            try {
                $filePath = storage_path("reports/{$slug}.md");
                if (File::exists($filePath)) {
                    File::delete($filePath);
                    $this->clearReportCache($slug);
                    $deleted[] = $slug;
                }
            } catch (\Exception $e) {
                $errors[] = $slug;
            }
        }
        
        return response()->json([
            'deleted' => $deleted,
            'errors' => $errors,
            'message' => count($deleted) . ' 个报告删除成功'
        ]);
    }
    
    /**
     * 清除报告缓存
     */
    public function clearCache($slug = null)
    {
        if ($slug) {
            $this->clearReportCache($slug);
            return response()->json(['message' => "报告 {$slug} 缓存已清除"]);
        } else {
            Cache::flush();
            return response()->json(['message' => '所有缓存已清除']);
        }
    }
    
    /**
     * 获取报告统计信息
     */
    public function stats()
    {
        $reportsDir = storage_path('reports');
        
        if (!File::exists($reportsDir)) {
            return response()->json([
                'total' => 0,
                'total_size' => 0,
                'latest' => null
            ]);
        }
        
        $files = File::glob($reportsDir . '/*.md');
        $totalSize = array_sum(array_map('filesize', $files));
        $latest = null;
        
        if (!empty($files)) {
            $latestFile = collect($files)->sortByDesc(function ($file) {
                return File::lastModified($file);
            })->first();
            
            $latest = [
                'name' => pathinfo($latestFile, PATHINFO_FILENAME),
                'modified' => File::lastModified($latestFile)
            ];
        }
        
        return response()->json([
            'total' => count($files),
            'total_size' => $this->formatFileSize($totalSize),
            'latest' => $latest
        ]);
    }
    
    // 私有辅助方法
    
    /**
     * 从内容生成slug
     */
    private function generateSlugFromContent($content, $originalName)
    {
        // 尝试从标题生成
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = trim($matches[1]);
            $slug = $this->slugify($title);
            if (!empty($slug)) {
                return $slug;
            }
        }
        
        // 使用原文件名
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        return $this->slugify($filename) ?: 'report-' . time();
    }
    
    /**
     * 生成URL友好的slug
     */
    private function slugify($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-_]/', '', $text);
        $text = preg_replace('/[\s-_]+/', '-', $text);
        return trim($text, '-');
    }
    
    /**
     * 提取摘要
     */
    private function extractExcerpt($content)
    {
        // 移除标题和元数据
        $content = preg_replace('/^#.*$/m', '', $content);
        $content = preg_replace('/<!--.*?-->/s', '', $content);
        $content = trim($content);
        
        // 获取第一段或前150个字符
        $paragraphs = explode("\n\n", $content);
        $firstParagraph = trim($paragraphs[0]);
        
        if (mb_strlen($firstParagraph) > 150) {
            return mb_substr($firstParagraph, 0, 150) . '...';
        }
        
        return $firstParagraph ?: '暂无摘要';
    }
    
    /**
     * 格式化文件大小
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
    
    /**
     * 清除特定报告的缓存
     */
    private function clearReportCache($slug)
    {
        $keys = Cache::getRedis()->keys("report.{$slug}.*");
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }
    
    /**
     * 生成HTML预览文件
     */
    private function generateHtmlPreview($slug, $content)
    {
        try {
            $html = $this->markdownConverter->convert($content);
            
            $htmlContent = "<!DOCTYPE html>
<html lang=\"zh-CN\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$slug}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2, h3, h4, h5, h6 { color: #333; }
        code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        blockquote { border-left: 4px solid #ddd; margin: 0; padding-left: 20px; color: #666; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    {$html}
</body>
</html>";
            
            File::put(public_path("{$slug}.html"), $htmlContent);
        } catch (\Exception $e) {
            Log::warning("无法生成HTML预览: " . $e->getMessage());
        }
    }
} 