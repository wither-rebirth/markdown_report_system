<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Storage;

class SitemapController extends Controller
{
    /**
     * 生成XML网站地图
     */
    public function sitemap()
    {
        $urls = [];
        
        // 静态页面
        $staticPages = [
            ['url' => route('home.index'), 'priority' => 1.0, 'changefreq' => 'daily'],
            ['url' => route('blog.index'), 'priority' => 0.9, 'changefreq' => 'daily'],
            ['url' => route('reports.categories'), 'priority' => 0.9, 'changefreq' => 'daily'],
            ['url' => route('aboutme.index'), 'priority' => 0.8, 'changefreq' => 'monthly'],
        ];
        
        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => $page['url'],
                'lastmod' => now()->toISOString(),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority']
            ];
        }
        
        // 博客文章
        $blogPosts = $this->getBlogPosts();
        foreach ($blogPosts as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post['slug']),
                'lastmod' => date('c', $post['mtime']),
                'changefreq' => 'weekly',
                'priority' => 0.7
            ];
        }
        
        // 报告页面
        $reports = $this->getReports();
        foreach ($reports as $report) {
            $urls[] = [
                'loc' => route('reports.show', $report['slug']),
                'lastmod' => date('c', $report['mtime']),
                'changefreq' => 'monthly',
                'priority' => 0.6
            ];
        }
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= '    <url>' . "\n";
            $xml .= '        <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
            $xml .= '        <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            $xml .= '        <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '        <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '    </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }
    
    /**
     * 生成RSS feed
     */
    public function rss()
    {
        $posts = $this->getBlogPosts(10); // 最新10篇文章
        
        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $rss .= '    <channel>' . "\n";
        $rss .= '        <title>Wither\'s Blog - 技术博客</title>' . "\n";
        $rss .= '        <link>' . route('blog.index') . '</link>' . "\n";
        $rss .= '        <description>专注于网络安全、渗透测试、编程开发等技术领域的原创博客</description>' . "\n";
        $rss .= '        <language>zh-CN</language>' . "\n";
        $rss .= '        <pubDate>' . now()->toRfc2822String() . '</pubDate>' . "\n";
        $rss .= '        <lastBuildDate>' . now()->toRfc2822String() . '</lastBuildDate>' . "\n";
        $rss .= '        <atom:link href="' . route('sitemap.rss') . '" rel="self" type="application/rss+xml" />' . "\n";
        $rss .= '        <generator>Wither\'s Blog</generator>' . "\n";
        $rss .= '        <webMaster>admin@witherblog.com (Wither)</webMaster>' . "\n";
        $rss .= '        <managingEditor>admin@witherblog.com (Wither)</managingEditor>' . "\n";
        $rss .= '        <category>Technology</category>' . "\n";
        $rss .= '        <ttl>60</ttl>' . "\n\n";
        
        foreach ($posts as $post) {
            $rss .= '        <item>' . "\n";
            $rss .= '            <title><![CDATA[' . $post['title'] . ']]></title>' . "\n";
            $rss .= '            <link>' . route('blog.show', $post['slug']) . '</link>' . "\n";
            $description = $post['excerpt'] ?: \Illuminate\Support\Str::limit(strip_tags($post['content'] ?? ''), 200);
            $rss .= '            <description><![CDATA[' . $description . ']]></description>' . "\n";
            $rss .= '            <author>' . $post['author'] . '</author>' . "\n";
            $rss .= '            <category>' . $post['category'] . '</category>' . "\n";
            $rss .= '            <pubDate>' . date('r', $post['published_at']) . '</pubDate>' . "\n";
            $rss .= '            <guid isPermaLink="true">' . route('blog.show', $post['slug']) . '</guid>' . "\n";
            $rss .= '        </item>' . "\n";
        }
        
        $rss .= '    </channel>' . "\n";
        $rss .= '</rss>';
        
        return response($rss, 200)
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }
    
    /**
     * 生成Atom feed
     */
    public function atom()
    {
        $posts = $this->getBlogPosts(10); // 最新10篇文章
        
        $atom = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $atom .= '<feed xmlns="http://www.w3.org/2005/Atom">' . "\n";
        $atom .= '    <title>Wither\'s Blog - 技术博客</title>' . "\n";
        $atom .= '    <link href="' . route('blog.index') . '" />' . "\n";
        $atom .= '    <link href="' . route('sitemap.atom') . '" rel="self" />' . "\n";
        $atom .= '    <updated>' . now()->toAtomString() . '</updated>' . "\n";
        $atom .= '    <id>' . route('blog.index') . '</id>' . "\n";
        $atom .= '    <subtitle>专注于网络安全、渗透测试、编程开发等技术领域的原创博客</subtitle>' . "\n";
        $atom .= '    <generator uri="' . route('home.index') . '" version="1.0">Wither\'s Blog</generator>' . "\n";
        $atom .= '    <rights>© ' . date('Y') . ' Wither\'s Blog</rights>' . "\n";
        $atom .= '    <author>' . "\n";
        $atom .= '        <name>Wither</name>' . "\n";
        $atom .= '        <email>admin@witherblog.com</email>' . "\n";
        $atom .= '        <uri>' . route('aboutme.index') . '</uri>' . "\n";
        $atom .= '    </author>' . "\n\n";
        
        foreach ($posts as $post) {
            $atom .= '    <entry>' . "\n";
            $atom .= '        <title type="text">' . htmlspecialchars($post['title']) . '</title>' . "\n";
            $atom .= '        <link href="' . route('blog.show', $post['slug']) . '" />' . "\n";
            $atom .= '        <id>' . route('blog.show', $post['slug']) . '</id>' . "\n";
            $atom .= '        <updated>' . date('c', $post['mtime']) . '</updated>' . "\n";
            $atom .= '        <published>' . date('c', $post['published_at']) . '</published>' . "\n";
            $summary = $post['excerpt'] ?: \Illuminate\Support\Str::limit(strip_tags($post['content'] ?? ''), 200);
            $atom .= '        <summary type="text">' . htmlspecialchars($summary) . '</summary>' . "\n";
            $atom .= '        <content type="html"><![CDATA[' . ($post['content'] ?? '') . ']]></content>' . "\n";
            $atom .= '        <author>' . "\n";
            $atom .= '            <name>' . $post['author'] . '</name>' . "\n";
            $atom .= '        </author>' . "\n";
            $atom .= '        <category term="' . htmlspecialchars($post['category']) . '" />' . "\n";
            foreach ($post['tags'] ?? [] as $tag) {
                $atom .= '        <category term="' . htmlspecialchars($tag) . '" />' . "\n";
            }
            $atom .= '    </entry>' . "\n";
        }
        
        $atom .= '</feed>';
        
        return response($atom, 200)
            ->header('Content-Type', 'application/atom+xml; charset=utf-8');
    }
    
    // robots.txt 现在是静态文件，不需要控制器方法
    
    /**
     * 获取博客文章列表
     */
    private function getBlogPosts($limit = null)
    {
        $posts = [];
        $blogPath = storage_path('app/blog');
        
        if (!is_dir($blogPath)) {
            return $posts;
        }
        
        $files = glob($blogPath . '/*.md');
        foreach ($files as $file) {
            $slug = basename($file, '.md');
            $content = file_get_contents($file);
            $stat = stat($file);
            
            // 解析front matter
            $frontMatter = $this->parseFrontMatter($content);
            
            $posts[] = [
                'slug' => $slug,
                'title' => $frontMatter['title'] ?? ucwords(str_replace('-', ' ', $slug)),
                'excerpt' => $frontMatter['excerpt'] ?? '',
                'content' => $content,
                'author' => $frontMatter['author'] ?? 'Wither',
                'category' => $frontMatter['category'] ?? '技术分享',
                'tags' => $frontMatter['tags'] ?? [],
                'published_at' => $frontMatter['published_at'] ?? $stat['mtime'],
                'mtime' => $stat['mtime'],
                'image' => $frontMatter['image'] ?? null,
                'reading_time' => $frontMatter['reading_time'] ?? ceil(str_word_count(strip_tags($content)) / 200)
            ];
        }
        
        // 按发布时间排序
        usort($posts, function($a, $b) {
            return $b['published_at'] <=> $a['published_at'];
        });
        
        return $limit ? array_slice($posts, 0, $limit) : $posts;
    }
    
    /**
     * 获取报告列表
     */
    private function getReports($limit = null)
    {
        $reports = [];
        $reportsPath = storage_path('reports');
        
        if (!is_dir($reportsPath)) {
            return $reports;
        }
        
        // 使用缓存提高性能，但确保能检测到文件变化
        $cacheKey = 'sitemap_reports_' . $this->generateReportsCacheKey($reportsPath);
        
        $reports = cache()->remember($cacheKey, 300, function () use ($reportsPath) {
            $allReports = [];
            
            // 处理传统的单个 .md 文件
            $files = glob($reportsPath . '/*.md');
            foreach ($files as $file) {
                $slug = basename($file, '.md');
                $stat = stat($file);
                $content = file_get_contents($file);
                
                // 解析front matter
                $frontMatter = $this->parseFrontMatter($content);
                
                $allReports[] = [
                    'slug' => $slug,
                    'title' => $frontMatter['title'] ?? ucwords(str_replace('-', ' ', $slug)),
                    'excerpt' => $frontMatter['excerpt'] ?? $this->extractExcerpt($content),
                    'mtime' => $stat['mtime'],
                    'size' => $stat['size']
                ];
            }
            
            // 处理 Hackthebox-Walkthrough 文件夹 - 支持新的难度分类结构
            $hacktheboxDir = storage_path('reports/Hackthebox-Walkthrough');
            if (is_dir($hacktheboxDir)) {
                $difficulties = ['Easy', 'Medium', 'Hard', 'Insane', 'Fortresses'];
                
                foreach ($difficulties as $difficulty) {
                    $difficultyDir = $hacktheboxDir . '/' . $difficulty;
                    if (is_dir($difficultyDir)) {
                        $machineDirectories = glob($difficultyDir . '/*', GLOB_ONLYDIR);
                        
                        foreach ($machineDirectories as $dir) {
                            $machineName = basename($dir);
                            $walkthroughFile = $dir . '/Walkthrough.md';
                            
                            // 检查是否存在 Walkthrough.md 文件
                            if (file_exists($walkthroughFile)) {
                                $stat = stat($walkthroughFile);
                                $content = file_get_contents($walkthroughFile);
                                
                                // 解析front matter
                                $frontMatter = $this->parseFrontMatter($content);
                                
                                // 使用实际文件修改时间
                                $mtime = filemtime($walkthroughFile);
                                
                                $title = $frontMatter['title'] ?? ($machineName . ' - HackTheBox ' . $difficulty . ' Writeup');
                                
                                $allReports[] = [
                                    'slug' => 'htb-' . $machineName,
                                    'title' => $title,
                                    'excerpt' => $frontMatter['excerpt'] ?? $this->extractExcerpt($content),
                                    'mtime' => $mtime,
                                    'size' => $stat['size'],
                                    'difficulty' => $difficulty
                                ];
                            }
                        }
                    }
                }
            }
            
            // 按修改时间排序
            usort($allReports, function($a, $b) {
                return $b['mtime'] <=> $a['mtime'];
            });
            
            return $allReports;
        });
        
        return $limit ? array_slice($reports, 0, $limit) : $reports;
    }
    
    /**
     * 生成报告缓存键，基于所有相关文件的最新修改时间
     */
    private function generateReportsCacheKey($reportsPath)
    {
        $latestMtime = 0;
        $fileCount = 0;
        
        // 检查普通报告文件
        if (is_dir($reportsPath)) {
            $reportFiles = glob($reportsPath . '/*.md');
            foreach ($reportFiles as $file) {
                $latestMtime = max($latestMtime, filemtime($file));
                $fileCount++;
            }
        }
        
        // 检查 Hackthebox 报告文件 - 支持新的难度分类结构
        $hacktheboxDir = $reportsPath . '/Hackthebox-Walkthrough';
        if (is_dir($hacktheboxDir)) {
            $difficulties = ['Easy', 'Medium', 'Hard', 'Insane', 'Fortresses'];
            
            foreach ($difficulties as $difficulty) {
                $difficultyDir = $hacktheboxDir . '/' . $difficulty;
                if (is_dir($difficultyDir)) {
                    $machineDirectories = glob($difficultyDir . '/*', GLOB_ONLYDIR);
                    foreach ($machineDirectories as $dir) {
                        $walkthroughFile = $dir . '/Walkthrough.md';
                        if (file_exists($walkthroughFile)) {
                            $latestMtime = max($latestMtime, filemtime($walkthroughFile));
                            $fileCount++;
                        }
                    }
                }
            }
        }
        
        // 组合缓存键：时间戳 + 文件数量
        return $latestMtime . '_' . $fileCount;
    }
    
    /**
     * 提取摘要
     */
    private function extractExcerpt($content)
    {
        // 移除 front matter
        $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
        
        // 移除 HTML 标签和 Markdown 语法
        $content = strip_tags($content);
        $content = preg_replace('/^#+\s+/m', '', $content); // 移除标题标记
        $content = preg_replace('/\*\*(.+?)\*\*/s', '$1', $content); // 移除粗体
        $content = preg_replace('/\*(.+?)\*/s', '$1', $content); // 移除斜体
        $content = preg_replace('/`(.+?)`/s', '$1', $content); // 移除代码标记
        $content = preg_replace('/\[(.+?)\]\(.+?\)/s', '$1', $content); // 移除链接
        
        // 获取第一段文字
        $lines = explode("\n", trim($content));
        $excerpt = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $excerpt = $line;
                break;
            }
        }
        
        // 如果没有找到有效的摘要，使用前100个字符
        if (empty($excerpt) && !empty($content)) {
            $excerpt = mb_substr(trim($content), 0, 100);
        }
        
        return $excerpt;
    }
    
    /**
     * 从内容中提取修改时间
     */
    private function extractModificationTime($content, $filePath)
    {
        $dates = [];
        $now = time();
        $oneYearFromNow = $now + (365 * 24 * 60 * 60); // 一年后
        
        // 尝试从内容中提取各种日期格式，但排除SSL证书等技术信息中的日期
        $patterns = [
            '/Date:\s*(\d{4}[-\/]\d{1,2}[-\/]\d{1,2})/i',
            '/更新时间.*?(\d{4}[-\/]\d{1,2}[-\/]\d{1,2})/i',
            '/修改时间.*?(\d{4}[-\/]\d{1,2}[-\/]\d{1,2})/i',
            '/创建时间.*?(\d{4}[-\/]\d{1,2}[-\/]\d{1,2})/i',
            '/发布时间.*?(\d{4}[-\/]\d{1,2}[-\/]\d{1,2})/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $match) {
                    try {
                        $timestamp = strtotime($match);
                        // 只接受合理的日期范围：2020年到一年后
                        if ($timestamp !== false && $timestamp > 0 
                            && $timestamp >= strtotime('2020-01-01') 
                            && $timestamp <= $oneYearFromNow) {
                            $dates[] = $timestamp;
                        }
                    } catch (\Exception $e) {
                        // 忽略无效日期
                    }
                }
            }
        }
        
        // 如果找到了合理的日期，返回最新的日期
        if (!empty($dates)) {
            return max($dates);
        }
        
        // 获取文件夹的修改时间
        $folderPath = dirname($filePath);
        if (is_dir($folderPath)) {
            $folderMtime = filemtime($folderPath);
            if ($folderMtime !== false && $folderMtime > 0) {
                return $folderMtime;
            }
        }
        
        // 如果都没有找到，返回文件的系统修改时间
        return filemtime($filePath);
    }
    
    /**
     * 解析front matter
     */
    private function parseFrontMatter($content)
    {
        $frontMatter = [];
        
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
            $yamlContent = $matches[1];
            $lines = explode("\n", $yamlContent);
            
            foreach ($lines as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // 处理数组格式的tags
                    if ($key === 'tags' && strpos($value, '[') === 0) {
                        $value = json_decode(str_replace("'", '"', $value), true) ?: [];
                    } elseif (is_numeric($value)) {
                        $value = (int) $value;
                    } elseif ($value === 'true') {
                        $value = true;
                    } elseif ($value === 'false') {
                        $value = false;
                    } else {
                        $value = trim($value, '"\'');
                    }
                    
                    $frontMatter[$key] = $value;
                }
            }
        }
        
        return $frontMatter;
    }
} 