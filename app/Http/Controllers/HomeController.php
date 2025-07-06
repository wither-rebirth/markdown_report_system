<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ReportController;

class HomeController extends Controller
{
    /**
     * 显示首页
     */
    public function index()
    {
        // 获取统计数据
        $stats = $this->getStats();
        
        // 获取最新内容
        $latestBlogPosts = $this->getLatestBlogPosts(3);
        $latestReports = $this->getLatestReports(3);
        
        // 获取技术栈信息
        $techStack = $this->getTechStack();
        
        // 获取最近活动
        $recentActivities = $this->getRecentActivities(5);
        
        return view('home.index', compact('stats', 'latestBlogPosts', 'latestReports', 'techStack', 'recentActivities'));
    }
    
    /**
     * 获取统计数据
     */
    private function getStats()
    {
        $cacheKey = 'home_stats';
        
        return Cache::remember($cacheKey, 300, function () {
            $stats = [
                'total_posts' => 0,
                'total_reports' => 0,
                'total_views' => 0,
                'total_size' => 0,
                'last_updated' => null
            ];
            
            // 统计博客文章
            $blogDir = storage_path('blog');
            if (File::exists($blogDir)) {
                $blogFiles = File::glob($blogDir . '/*.md');
                $blogDirs = File::directories($blogDir);
                $stats['total_posts'] = count($blogFiles) + count($blogDirs);
                
                // 计算总大小
                foreach ($blogFiles as $file) {
                    $stats['total_size'] += File::size($file);
                }
                foreach ($blogDirs as $dir) {
                    $indexFile = $dir . '/index.md';
                    if (File::exists($indexFile)) {
                        $stats['total_size'] += File::size($indexFile);
                    }
                }
            }
            
            // 统计报告
            $reportsDir = storage_path('reports');
            if (File::exists($reportsDir)) {
                $reportFiles = File::glob($reportsDir . '/*.md');
                $hacktheboxDir = $reportsDir . '/Hackthebox-Walkthrough';
                $htbReports = 0;
                
                if (File::exists($hacktheboxDir)) {
                    $htbDirs = File::directories($hacktheboxDir);
                    foreach ($htbDirs as $dir) {
                        if (File::exists($dir . '/Walkthrough.md')) {
                            $htbReports++;
                            $stats['total_size'] += File::size($dir . '/Walkthrough.md');
                        }
                    }
                }
                
                $stats['total_reports'] = count($reportFiles) + $htbReports;
                
                // 计算总大小
                foreach ($reportFiles as $file) {
                    $stats['total_size'] += File::size($file);
                }
            }
            
            // 获取最后更新时间
            $lastModified = 0;
            if (File::exists($blogDir)) {
                $allFiles = File::allFiles($blogDir);
                foreach ($allFiles as $file) {
                    $lastModified = max($lastModified, File::lastModified($file));
                }
            }
            if (File::exists($reportsDir)) {
                $allFiles = File::allFiles($reportsDir);
                foreach ($allFiles as $file) {
                    $lastModified = max($lastModified, File::lastModified($file));
                }
            }
            
            $stats['last_updated'] = $lastModified;
            $stats['total_views'] = rand(1000, 9999); // 模拟访问量
            
            return $stats;
        });
    }
    
    /**
     * 获取最新博客文章
     */
    private function getLatestBlogPosts($limit = 3)
    {
        $cacheKey = 'latest_blog_posts_' . $limit;
        
        return Cache::remember($cacheKey, 300, function () use ($limit) {
            $posts = [];
            $blogDir = storage_path('blog');
            
            if (!File::exists($blogDir)) {
                return $posts;
            }
            
            // 获取所有 .md 文件
            $files = File::glob($blogDir . '/*.md');
            $allFiles = [];
            
            foreach ($files as $file) {
                $allFiles[] = [
                    'path' => $file,
                    'modified' => File::lastModified($file)
                ];
            }
            
            // 获取文件夹类型的博客
            $directories = File::directories($blogDir);
            foreach ($directories as $dir) {
                $indexFile = $dir . '/index.md';
                if (File::exists($indexFile)) {
                    $allFiles[] = [
                        'path' => $indexFile,
                        'modified' => File::lastModified($indexFile)
                    ];
                }
            }
            
            // 按修改时间排序
            usort($allFiles, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
            
            // 取前几个文件
            $latestFiles = array_slice($allFiles, 0, $limit);
            
            foreach ($latestFiles as $fileInfo) {
                $content = File::get($fileInfo['path']);
                $filename = pathinfo($fileInfo['path'], PATHINFO_FILENAME);
                
                // 解析前置元数据
                $metadata = $this->parseMetadata($content);
                
                $posts[] = [
                    'slug' => $filename === 'index' ? basename(dirname($fileInfo['path'])) : $filename,
                    'title' => $metadata['title'] ?? $filename,
                    'excerpt' => $metadata['excerpt'] ?? $this->extractExcerpt($content),
                    'author' => $metadata['author'] ?? 'Admin',
                    'category' => $metadata['category'] ?? 'Technology',
                    'image' => $metadata['image'] ?? null,
                    'published_at' => $metadata['date'] ?? $fileInfo['modified'],
                    'reading_time' => $this->calculateReadingTime($content),
                ];
            }
            
            return $posts;
        });
    }
    
    /**
     * 获取最新报告
     */
    private function getLatestReports($limit = 3)
    {
        $cacheKey = 'latest_reports_' . $limit;
        
        return Cache::remember($cacheKey, 300, function () use ($limit) {
            $reports = [];
            $reportsDir = storage_path('reports');
            
            if (!File::exists($reportsDir)) {
                return $reports;
            }
            
            $allFiles = [];
            
            // 获取普通报告
            $files = File::glob($reportsDir . '/*.md');
            foreach ($files as $file) {
                $allFiles[] = [
                    'path' => $file,
                    'type' => 'normal',
                    'modified' => File::lastModified($file)
                ];
            }
            
            // 获取 Hackthebox 报告
            $hacktheboxDir = $reportsDir . '/Hackthebox-Walkthrough';
            if (File::exists($hacktheboxDir)) {
                $directories = File::directories($hacktheboxDir);
                foreach ($directories as $dir) {
                    $walkthroughFile = $dir . '/Walkthrough.md';
                    if (File::exists($walkthroughFile)) {
                        $allFiles[] = [
                            'path' => $walkthroughFile,
                            'type' => 'hackthebox',
                            'folder' => basename($dir),
                            'modified' => File::lastModified($walkthroughFile)
                        ];
                    }
                }
            }
            
            // 按修改时间排序
            usort($allFiles, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
            
            // 取前几个文件
            $latestFiles = array_slice($allFiles, 0, $limit);
            
            foreach ($latestFiles as $fileInfo) {
                $content = File::get($fileInfo['path']);
                
                if ($fileInfo['type'] === 'hackthebox') {
                    $reports[] = [
                        'slug' => 'htb-' . $fileInfo['folder'],
                        'title' => $fileInfo['folder'],
                        'excerpt' => $this->extractExcerpt($content),
                        'mtime' => $fileInfo['modified'],
                        'size' => File::size($fileInfo['path']),
                        'type' => 'hackthebox'
                    ];
                } else {
                    $filename = pathinfo($fileInfo['path'], PATHINFO_FILENAME);
                    $reports[] = [
                        'slug' => $filename,
                        'title' => $this->extractTitleFromContent($content) ?: $filename,
                        'excerpt' => $this->extractExcerpt($content),
                        'mtime' => $fileInfo['modified'],
                        'size' => File::size($fileInfo['path']),
                        'type' => 'normal'
                    ];
                }
            }
            
            return $reports;
        });
    }
    
    /**
     * 获取技术栈信息
     */
    private function getTechStack()
    {
        return [
            'languages' => [
                ['name' => 'PHP', 'level' => 85, 'color' => '#777BB4'],
                ['name' => 'Python', 'level' => 80, 'color' => '#3776AB'],
                ['name' => 'JavaScript', 'level' => 75, 'color' => '#F7DF1E'],
                ['name' => 'SQL', 'level' => 70, 'color' => '#336791'],
                ['name' => 'Bash', 'level' => 65, 'color' => '#4EAA25'],
            ],
            'frameworks' => [
                ['name' => 'Laravel', 'level' => 85, 'color' => '#FF2D20'],
                ['name' => 'Vue.js', 'level' => 70, 'color' => '#4FC08D'],
                ['name' => 'Express.js', 'level' => 60, 'color' => '#000000'],
            ],
            'tools' => [
                ['name' => 'Burp Suite', 'level' => 80, 'color' => '#FF6633'],
                ['name' => 'Nmap', 'level' => 75, 'color' => '#009639'],
                ['name' => 'Metasploit', 'level' => 70, 'color' => '#1976D2'],
                ['name' => 'Docker', 'level' => 65, 'color' => '#2496ED'],
            ],
        ];
    }
    
    /**
     * 获取最近活动
     */
    private function getRecentActivities($limit = 5)
    {
        $activities = [];
        
        // 获取最新文件更新
        $blogDir = storage_path('blog');
        $reportsDir = storage_path('reports');
        
        $allFiles = [];
        
        if (File::exists($blogDir)) {
            $files = File::allFiles($blogDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
                    $allFiles[] = [
                        'path' => $file,
                        'type' => 'blog',
                        'name' => pathinfo($file, PATHINFO_FILENAME),
                        'modified' => File::lastModified($file)
                    ];
                }
            }
        }
        
        if (File::exists($reportsDir)) {
            $files = File::allFiles($reportsDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
                    $allFiles[] = [
                        'path' => $file,
                        'type' => 'report',
                        'name' => pathinfo($file, PATHINFO_FILENAME),
                        'modified' => File::lastModified($file)
                    ];
                }
            }
        }
        
        // 按修改时间排序
        usort($allFiles, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
        
        // 生成活动记录
        foreach (array_slice($allFiles, 0, $limit) as $file) {
            $activities[] = [
                'type' => $file['type'],
                'action' => '更新',
                'name' => $file['name'],
                'time' => $file['modified'],
                'time_ago' => $this->timeAgo($file['modified'])
            ];
        }
        
        return $activities;
    }
    
    /**
     * 时间距离现在的描述
     */
    private function timeAgo($timestamp)
    {
        $time = time() - $timestamp;
        
        if ($time < 60) {
            return '刚刚';
        } elseif ($time < 3600) {
            return floor($time / 60) . '分钟前';
        } elseif ($time < 86400) {
            return floor($time / 3600) . '小时前';
        } elseif ($time < 2592000) {
            return floor($time / 86400) . '天前';
        } elseif ($time < 31536000) {
            return floor($time / 2592000) . '个月前';
        } else {
            return floor($time / 31536000) . '年前';
        }
    }
    
    /**
     * 解析文章前置元数据
     */
    private function parseMetadata($content)
    {
        $metadata = [];
        
        // 使用更健壮的正则表达式匹配YAML前言
        $patterns = [
            '/^---\s*[\r\n]+(.*?)[\r\n]+---\s*[\r\n]+/s',
            '/^---.*?[\r\n]+(.*?)[\r\n]+---\s*[\r\n]*/s',
            '/^---(.*?)---/s'
        ];
        
        $yamlContent = '';
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $yamlContent = $matches[1];
                break;
            }
        }
        
        if (!empty($yamlContent)) {
            $lines = explode("\n", $yamlContent);
            
            foreach ($lines as $line) {
                if (preg_match('/^(\w+):\s*(.+)$/', trim($line), $lineMatches)) {
                    $key = $lineMatches[1];
                    $value = trim($lineMatches[2], '"\'');
                    
                    if ($key === 'tags') {
                        $metadata[$key] = array_map('trim', explode(',', $value));
                    } elseif ($key === 'date') {
                        $metadata[$key] = strtotime($value);
                    } else {
                        $metadata[$key] = $value;
                    }
                }
            }
        }
        
        return $metadata;
    }
    
    /**
     * 提取文章摘要
     */
    private function extractExcerpt($content)
    {
        // 移除前置元数据
        $content = $this->removeFrontMatter($content);
        
        // 移除Markdown标记
        $content = preg_replace('/[#*`_\[\]()]/', '', $content);
        $content = preg_replace('/!\[.*?\]\(.*?\)/', '', $content);
        $content = preg_replace('/\[.*?\]\(.*?\)/', '', $content);
        
        // 获取前150个字符
        $excerpt = mb_substr(trim($content), 0, 150);
        
        return $excerpt ? $excerpt . '...' : '暂无摘要';
    }
    
    /**
     * 移除前言部分
     */
    private function removeFrontMatter($content)
    {
        $patterns = [
            '/^---\s*[\r\n]+.*?[\r\n]+---\s*[\r\n]+/s',
            '/^---.*?---\s*[\r\n]*/s',
            '/^---.*?---/s'
        ];
        
        $cleaned = $content;
        foreach ($patterns as $pattern) {
            $result = preg_replace($pattern, '', $cleaned);
            if ($result !== $cleaned) {
                $cleaned = $result;
                break;
            }
        }
        
        return ltrim($cleaned, "\r\n\t ");
    }
    
    /**
     * 计算阅读时间
     */
    private function calculateReadingTime($content)
    {
        $wordCount = str_word_count(strip_tags($content));
        $readingTime = ceil($wordCount / 200); // 假设每分钟阅读200个单词
        
        return max(1, $readingTime);
    }
    
    /**
     * 从内容中提取标题
     */
    private function extractTitleFromContent($content)
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
} 