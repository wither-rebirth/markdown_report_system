<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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
     * 显示报告列表 - 支持分页和搜索
     */
    public function index(Request $request)
    {
        $reportsDir = storage_path('reports');
        $hacktheboxDir = storage_path('reports/Hackthebox-Walkthrough');
        
        if (!File::exists($reportsDir)) {
            File::makeDirectory($reportsDir, 0755, true);
        }
        
        // 获取搜索查询参数
        $searchQuery = $request->input('search');
        
        // 使用更精确的缓存键，包含所有相关文件的最新修改时间
        $cacheKey = 'all_reports_' . $this->generateReportsCacheKey($reportsDir, $hacktheboxDir);
        $allReports = Cache::remember($cacheKey, 600, function () use ($reportsDir, $hacktheboxDir) {
            $reports = collect();
            
            // 处理传统的单个 .md 文件
            $mdFiles = collect(File::glob($reportsDir . '/*.md'))
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
                        'content' => $content, // 保存完整内容用于搜索
                        'mtime' => File::lastModified($file),
                        'size' => File::size($file),
                        'status' => 'active',
                        'type' => 'file'
                    ];
                });
            
            // 处理 Hackthebox-Walkthrough 文件夹
            if (File::exists($hacktheboxDir) && File::isDirectory($hacktheboxDir)) {
                $hacktheboxReports = $this->getHacktheboxReports($hacktheboxDir);
                $reports = $reports->merge($hacktheboxReports);
            }
            
            // 合并并排序
            return $reports->merge($mdFiles)
                ->sortByDesc('mtime')
                ->values()
                ->toArray();
        });
        
        // 应用搜索过滤
        if (!empty($searchQuery)) {
            $allReports = $this->filterReportsBySearch($allReports, $searchQuery);
        }
        
        // 分页设置 - 固定每页10个
        $perPage = 10; // 固定每页显示10个
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($allReports, ($currentPage - 1) * $perPage, $perPage);
        
        // 创建分页器
        $reports = new LengthAwarePaginator(
            $currentItems,
            count($allReports),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        
        // 保留查询参数
        $reports->appends($request->query());
        
        return view('report.index', compact('reports'));
    }
    
    /**
     * 生成报告缓存键，基于所有相关文件的最新修改时间
     */
    private function generateReportsCacheKey($reportsDir, $hacktheboxDir)
    {
        $latestMtime = 0;
        $fileCount = 0;
        
        // 检查普通报告文件
        if (File::exists($reportsDir)) {
            $reportFiles = File::glob($reportsDir . '/*.md');
            foreach ($reportFiles as $file) {
                $latestMtime = max($latestMtime, File::lastModified($file));
                $fileCount++;
            }
        }
        
        // 检查 Hackthebox 报告文件
        if (File::exists($hacktheboxDir) && File::isDirectory($hacktheboxDir)) {
            $directories = File::directories($hacktheboxDir);
            foreach ($directories as $dir) {
                $walkthroughFile = $dir . '/Walkthrough.md';
                if (File::exists($walkthroughFile)) {
                    $latestMtime = max($latestMtime, File::lastModified($walkthroughFile));
                    $fileCount++;
                }
            }
        }
        
        // 组合缓存键：时间戳 + 文件数量
        return $latestMtime . '_' . $fileCount;
    }
    
    /**
     * 清除所有报告相关缓存
     */
    public function clearAllReportsCache()
    {
        try {
            // 清除报告列表缓存
            $this->clearReportListCache();
            
            // 清除单个报告缓存
            $this->clearIndividualReportsCache();
            
            // 清除首页缓存
            Cache::forget('home_stats');
            Cache::forget('latest_reports_3');
            
            return response()->json(['message' => '所有报告缓存已清除']);
        } catch (\Exception $e) {
            Log::error('清除报告缓存失败: ' . $e->getMessage());
            return response()->json(['error' => '清除缓存失败'], 500);
        }
    }
    
    /**
     * 清除报告列表缓存
     */
    private function clearReportListCache()
    {
        // 使用 Redis keys 命令查找所有相关缓存键
        $cacheKeys = Cache::getRedis()->keys('all_reports_*');
        if (!empty($cacheKeys)) {
            Cache::getRedis()->del($cacheKeys);
        }
    }
    
    /**
     * 清除所有单个报告的缓存
     */
    private function clearIndividualReportsCache()
    {
        // 清除普通报告缓存
        $reportKeys = Cache::getRedis()->keys('report.*');
        if (!empty($reportKeys)) {
            Cache::getRedis()->del($reportKeys);
        }
        
        // 清除 HackTheBox 报告缓存
        $htbKeys = Cache::getRedis()->keys('htb.report.*');
        if (!empty($htbKeys)) {
            Cache::getRedis()->del($htbKeys);
        }
    }
    
    /**
     * 根据搜索查询过滤报告
     */
    private function filterReportsBySearch($reports, $searchQuery)
    {
        $searchQuery = mb_strtolower(trim($searchQuery));
        if (empty($searchQuery)) {
            return $reports;
        }
        
        return collect($reports)->filter(function ($report) use ($searchQuery) {
            // 搜索标题
            $title = mb_strtolower($report['title']);
            if (mb_strpos($title, $searchQuery) !== false) {
                return true;
            }
            
            // 搜索摘要
            $excerpt = mb_strtolower($report['excerpt'] ?? '');
            if (mb_strpos($excerpt, $searchQuery) !== false) {
                return true;
            }
            
            // 搜索内容（如果有）
            $content = mb_strtolower($report['content'] ?? '');
            if (mb_strpos($content, $searchQuery) !== false) {
                return true;
            }
            
            // 搜索文件夹名（用于Hackthebox报告）
            if (isset($report['folder_name'])) {
                $folderName = mb_strtolower($report['folder_name']);
                if (mb_strpos($folderName, $searchQuery) !== false) {
                    return true;
                }
            }
            
            return false;
        })->values()->toArray();
    }

    /**
     * 获取 Hackthebox-Walkthrough 文件夹中的报告
     */
    private function getHacktheboxReports($hacktheboxDir)
    {
        $reports = collect();
        
        // 读取所有子文件夹
        $directories = File::directories($hacktheboxDir);
        
        foreach ($directories as $dir) {
            $dirName = basename($dir);
            $walkthroughFile = $dir . '/Walkthrough.md';
            $imagesDir = $dir . '/images';
            
            // 检查是否存在 Walkthrough.md 文件
            if (File::exists($walkthroughFile)) {
                $content = File::get($walkthroughFile);
                $excerpt = $this->extractExcerpt($content);
                $mtime = $this->extractModificationTime($content, $walkthroughFile);
                $size = File::size($walkthroughFile);
                
                // 统计图片数量
                $imageCount = 0;
                if (File::exists($imagesDir) && File::isDirectory($imagesDir)) {
                    $imageFiles = File::glob($imagesDir . '/*.{jpg,jpeg,png,gif,bmp,webp}', GLOB_BRACE);
                    $imageCount = count($imageFiles);
                }
                
                $reports->push([
                    'slug' => 'htb-' . $dirName,
                    'title' => $dirName,
                    'excerpt' => $excerpt,
                    'content' => $content, // 保存完整内容用于搜索
                    'mtime' => $mtime,
                    'size' => $size,
                    'status' => 'active',
                    'type' => 'hackthebox',
                    'folder_name' => $dirName,
                    'image_count' => $imageCount,
                    'has_images' => $imageCount > 0
                ]);
            }
        }
        
        return $reports;
    }
    

    
    /**
     * 显示单个报告
     */
    public function show($slug)
    {
        // 检查是否是 Hackthebox 报告
        if (str_starts_with($slug, 'htb-')) {
            return $this->showHacktheboxReport($slug);
        }
        
        $filePath = storage_path("reports/{$slug}.md");
        
        if (!File::exists($filePath)) {
            abort(404, '报告不存在');
        }
        
        // 使用缓存提高性能
        $cacheKey = "report.{$slug}." . File::lastModified($filePath) . '.v2';
        
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
            
            // 提取SEO数据
            $excerpt = $this->extractExcerpt($content);
            $keywords = $this->extractKeywords($content, $title);
            
            return [
                'title' => $title,
                'html' => $html,
                'slug' => $slug,
                'mtime' => File::lastModified($filePath),
                'size' => File::size($filePath),
                'excerpt' => $excerpt,
                'keywords' => $keywords,
                'type' => 'report',
                'full_title' => $title,
                'canonical_url' => route('reports.show', $slug),
            ];
        });
        
        return view('report.show', $data);
    }
    
    /**
     * 显示 Hackthebox 报告
     */
    private function showHacktheboxReport($slug)
    {
        // 提取文件夹名（去掉 htb- 前缀）
        $folderName = substr($slug, 4);
        $reportDir = storage_path("reports/Hackthebox-Walkthrough/{$folderName}");
        $walkthroughFile = $reportDir . '/Walkthrough.md';
        
        if (!File::exists($walkthroughFile)) {
            abort(404, '报告不存在');
        }
        
        // 使用缓存提高性能
        $cacheKey = "htb.report.{$slug}." . File::lastModified($walkthroughFile) . '.v2';
        
        $data = Cache::remember($cacheKey, 3600, function () use ($walkthroughFile, $slug, $folderName, $reportDir) {
            $content = File::get($walkthroughFile);
            
            // 处理图片链接 - 将相对路径转换为可访问的URL
            $content = $this->processHacktheboxImages($content, $folderName);
            
            // 转换 Markdown 为 HTML
            $html = $this->markdownConverter->convert($content);
            
            // 统计图片数量
            $imagesDir = $reportDir . '/images';
            $imageCount = 0;
            if (File::exists($imagesDir) && File::isDirectory($imagesDir)) {
                $imageFiles = File::glob($imagesDir . '/*.{jpg,jpeg,png,gif,bmp,webp}', GLOB_BRACE);
                $imageCount = count($imageFiles);
            }
            
            // 提取SEO数据
            $excerpt = $this->extractExcerpt($content);
            $keywords = $this->extractKeywords($content, $folderName);
            
            return [
                'title' => $folderName,
                'html' => $html,
                'slug' => $slug,
                'mtime' => File::lastModified($walkthroughFile),
                'size' => File::size($walkthroughFile),
                'type' => 'hackthebox',
                'folder_name' => $folderName,
                'image_count' => $imageCount,
                'excerpt' => $excerpt,
                'keywords' => $keywords,
                'full_title' => $folderName . ' - HackTheBox Writeup',
                'canonical_url' => route('reports.show', $slug),
            ];
        });
        
        return view('report.show', $data);
    }
    
    /**
     * 处理 Hackthebox 报告中的图片链接
     */
    private function processHacktheboxImages($content, $folderName)
    {
        // 处理 Markdown 图片语法 ![alt](images/filename.ext)
        $content = preg_replace_callback(
            '/!\[([^\]]*)\]\(images\/([^)]+)\)/',
            function ($matches) use ($folderName) {
                $alt = $matches[1];
                $filename = $matches[2];
                $url = route('reports.htb-image', ['folder' => $folderName, 'filename' => $filename]);
                return "![{$alt}]({$url})";
            },
            $content
        );
        
        // 处理 HTML img 标签 <img src="images/filename.ext">
        $content = preg_replace_callback(
            '/<img([^>]*?)src=["\']images\/([^"\']+)["\']([^>]*?)>/i',
            function ($matches) use ($folderName) {
                $before = $matches[1];
                $filename = $matches[2];
                $after = $matches[3];
                $url = route('reports.htb-image', ['folder' => $folderName, 'filename' => $filename]);
                return "<img{$before}src=\"{$url}\"{$after}>";
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * 提供 Hackthebox 报告图片
     */
    public function getHacktheboxImage($folder, $filename)
    {
        // URL解码文件名
        $decodedFilename = urldecode($filename);
        $imagePath = storage_path("reports/Hackthebox-Walkthrough/{$folder}/images/{$decodedFilename}");
        
        if (!File::exists($imagePath)) {
            abort(404, '图片不存在');
        }
        
        // 检查文件类型
        $mimeType = mime_content_type($imagePath);
        if (!str_starts_with($mimeType, 'image/')) {
            abort(403, '文件类型不支持');
        }
        
        return response()->file($imagePath);
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
        // 1. 优先检查YAML front matter中的description
        if (preg_match('/^---\s*\n.*?description:\s*[\'"]?(.*?)[\'"]?\s*\n.*?---\s*\n/s', $content, $matches)) {
            $description = trim($matches[1]);
            if (!empty($description)) {
                return $description;
            }
        }
        
        // 2. 检查markdown中的Description章节
        if (preg_match('/^#{1,6}\s*Description\s*\n(.*?)(?=\n#{1,6}|\n\n|\Z)/sim', $content, $matches)) {
            $description = trim($matches[1]);
            if (!empty($description)) {
                // 移除markdown语法并限制长度
                $description = strip_tags($description);
                $description = preg_replace('/\*\*(.*?)\*\*/', '$1', $description);
                $description = preg_replace('/\*(.*?)\*/', '$1', $description);
                $description = preg_replace('/`(.*?)`/', '$1', $description);
                
                if (mb_strlen($description) > 200) {
                    return mb_substr($description, 0, 200) . '...';
                }
                return $description;
            }
        }
        
        // 3. 检查特定的description块（如果有特殊格式）
        if (preg_match('/^description:\s*(.+)$/m', $content, $matches)) {
            $description = trim($matches[1]);
            if (!empty($description)) {
                return $description;
            }
        }
        
        // 4. 如果没有找到description块，则使用原有的excerpt逻辑
        $processedContent = $content;
        
        // 移除标题和元数据
        $processedContent = preg_replace('/^#.*$/m', '', $processedContent);
        $processedContent = preg_replace('/<!--.*?-->/s', '', $processedContent);
        $processedContent = preg_replace('/^---\s*\n.*?---\s*\n/s', '', $processedContent);
        $processedContent = trim($processedContent);
        
        // 获取第一段或前150个字符
        $paragraphs = explode("\n\n", $processedContent);
        $firstParagraph = trim($paragraphs[0]);
        
        if (mb_strlen($firstParagraph) > 150) {
            return mb_substr($firstParagraph, 0, 150) . '...';
        }
        
        // 5. 最后兜底，如果什么都没有，返回null而不是默认描述
        return !empty($firstParagraph) ? $firstParagraph : null;
    }
    
    /**
     * 提取关键词
     */
    private function extractKeywords($content, $title)
    {
        $keywords = [];
        
        // 基础关键词
        $keywords[] = 'Wither';
        $keywords[] = 'Penetration Testing';
        $keywords[] = 'Cybersecurity';
        $keywords[] = 'Security Research';
        
        // 根据类型添加关键词
        $lowerContent = mb_strtolower($content);
        $lowerTitle = mb_strtolower($title);
        
        // HackTheBox 相关
        if (strpos($lowerContent, 'hackthebox') !== false || strpos($lowerTitle, 'hackthebox') !== false) {
            $keywords[] = 'HackTheBox';
            $keywords[] = 'HTB';
            $keywords[] = 'Writeup';
            $keywords[] = 'Walkthrough';
            $keywords[] = 'CTF';
        }
        
        // 常见安全术语
        $securityTerms = [
            'sql injection' => 'SQL Injection',
            'xss' => 'XSS',
            'csrf' => 'CSRF',
            'lfi' => 'LFI',
            'rfi' => 'RFI',
            'privilege escalation' => 'Privilege Escalation',
            'buffer overflow' => 'Buffer Overflow',
            'reverse shell' => 'Reverse Shell',
            'web shell' => 'Web Shell',
            'enumeration' => 'Enumeration',
            'reconnaissance' => 'Reconnaissance',
            'nmap' => 'Nmap',
            'burp suite' => 'Burp Suite',
            'metasploit' => 'Metasploit',
            'gobuster' => 'Gobuster',
            'dirb' => 'Dirb',
            'nikto' => 'Nikto',
            'sqlmap' => 'SQLMap',
            'hydra' => 'Hydra',
            'john' => 'John the Ripper',
            'hashcat' => 'Hashcat',
            'steganography' => 'Steganography',
            'cryptography' => 'Cryptography',
            'forensics' => 'Digital Forensics',
            'malware' => 'Malware Analysis',
            'reverse engineering' => 'Reverse Engineering',
        ];
        
        foreach ($securityTerms as $term => $keyword) {
            if (strpos($lowerContent, $term) !== false || strpos($lowerTitle, $term) !== false) {
                $keywords[] = $keyword;
            }
        }
        
        // 添加标题中的关键词
        $titleWords = explode(' ', $title);
        foreach ($titleWords as $word) {
            $cleanWord = preg_replace('/[^a-zA-Z0-9\-]/', '', $word);
            if (strlen($cleanWord) > 3) {
                $keywords[] = $cleanWord;
            }
        }
        
        // 去重并返回
        return implode(', ', array_unique($keywords));
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

    /**
     * 从文件内容中提取修改时间
     */
    private function extractModificationTime($content, $filePath)
    {
        $dates = [];
        
        // 首先查找图片文件名中的时间戳格式 (YYYYMMDDHHMMSS)
        if (preg_match_all('/Pasted%20image%20(\d{14})/', $content, $matches)) {
            foreach ($matches[1] as $timestamp) {
                // 解析 YYYYMMDDHHMMSS 格式
                $year = substr($timestamp, 0, 4);
                $month = substr($timestamp, 4, 2);
                $day = substr($timestamp, 6, 2);
                $hour = substr($timestamp, 8, 2);
                $minute = substr($timestamp, 10, 2);
                $second = substr($timestamp, 12, 2);
                
                // 验证日期有效性
                if (checkdate($month, $day, $year)) {
                    $dateTime = mktime($hour, $minute, $second, $month, $day, $year);
                    if ($dateTime !== false) {
                        $dates[] = $dateTime;
                    }
                }
            }
        }
        
        // 如果找到了图片时间戳，返回最新的一个（这是最准确的时间）
        if (!empty($dates)) {
            return max($dates);
        }
        
        // 备选方案：尝试其他日期格式
        $patterns = [
            // YYYY-MM-DD 格式
            '/(\d{4}-\d{1,2}-\d{1,2})/',
            // DD/MM/YYYY 或 MM/DD/YYYY 格式
            '/(\d{1,2}\/\d{1,2}\/\d{4})/',
            // Mon May 14 2018 格式
            '/([A-Za-z]{3}\s+[A-Za-z]{3}\s+\d{1,2}\s+\d{4})/',
            // Nov 20, 2018 格式
            '/([A-Za-z]{3}\s+\d{1,2},?\s+\d{4})/',
            // 2018-11-20 11:57 格式
            '/(\d{4}-\d{1,2}-\d{1,2}\s+\d{1,2}:\d{2})/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $match) {
                    try {
                        $timestamp = strtotime($match);
                        if ($timestamp !== false && $timestamp > 0) {
                            $dates[] = $timestamp;
                        }
                    } catch (\Exception $e) {
                        // 忽略无效日期
                    }
                }
            }
        }
        
        // 如果找到了其他格式的日期，返回最新的日期
        if (!empty($dates)) {
            return max($dates);
        }
        
        // 最后的备选方案：从文件夹名称中提取日期信息
        $folderName = basename(dirname($filePath));
        if (preg_match('/(\d{4})[_-]?(\d{1,2})[_-]?(\d{1,2})/', $folderName, $matches)) {
            $timestamp = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
            if ($timestamp !== false) {
                return $timestamp;
            }
        }
        
        // 如果都没有找到，返回文件的系统修改时间
        return File::lastModified($filePath);
    }
    
    /**
     * 生成XML sitemap
     */
    public function sitemap()
    {
        $reports = $this->getAllReportsForSitemap();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // 添加主页
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . route('home.index') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s\Z') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>daily</changefreq>' . "\n";
        $xml .= '    <priority>1.0</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // 添加报告列表页
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . route('reports.index') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s\Z') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.9</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // 添加博客页
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . route('blog.index') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s\Z') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.9</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // 添加关于我页
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . route('aboutme.index') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s\Z') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>monthly</changefreq>' . "\n";
        $xml .= '    <priority>0.8</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // 添加所有报告
        foreach ($reports as $report) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . route('reports.show', $report['slug']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s\Z', $report['mtime']) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.7</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return response($xml)->header('Content-Type', 'application/xml');
    }
    
    /**
     * 生成robots.txt
     */
    public function robots()
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /api/\n";
        $content .= "Disallow: /*.json\n";
        $content .= "Disallow: /storage/\n";
        $content .= "\n";
        $content .= "# Sitemap\n";
        $content .= "Sitemap: " . route('sitemap') . "\n";
        $content .= "\n";
        $content .= "# Crawl-delay\n";
        $content .= "Crawl-delay: 1\n";
        
        return response($content)->header('Content-Type', 'text/plain');
    }
    
    /**
     * 获取所有报告用于sitemap
     */
    private function getAllReportsForSitemap()
    {
        $reportsDir = storage_path('reports');
        $hacktheboxDir = storage_path('reports/Hackthebox-Walkthrough');
        
        if (!File::exists($reportsDir)) {
            return [];
        }
        
        $reports = collect();
        
        // 处理传统的单个 .md 文件
        $mdFiles = collect(File::glob($reportsDir . '/*.md'))
            ->map(function ($file) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                return [
                    'slug' => $filename,
                    'mtime' => File::lastModified($file),
                ];
            });
        
        // 处理 Hackthebox-Walkthrough 文件夹
        if (File::exists($hacktheboxDir) && File::isDirectory($hacktheboxDir)) {
            $directories = File::directories($hacktheboxDir);
            
            foreach ($directories as $dir) {
                $dirName = basename($dir);
                $walkthroughFile = $dir . '/Walkthrough.md';
                
                if (File::exists($walkthroughFile)) {
                    $reports->push([
                        'slug' => 'htb-' . $dirName,
                        'mtime' => File::lastModified($walkthroughFile),
                    ]);
                }
            }
        }
        
        return $reports->merge($mdFiles)->sortByDesc('mtime')->toArray();
    }
} 