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
            ['url' => route('reports.index'), 'priority' => 0.9, 'changefreq' => 'daily'],
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
    
    /**
     * 生成robots.txt
     */
    public function robots()
    {
        $robots = view('sitemap.robots')->render();
        
        return response($robots, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
    
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
        
        $files = glob($reportsPath . '/*.md');
        foreach ($files as $file) {
            $slug = basename($file, '.md');
            $stat = stat($file);
            $content = file_get_contents($file);
            
            // 解析front matter
            $frontMatter = $this->parseFrontMatter($content);
            
            $reports[] = [
                'slug' => $slug,
                'title' => $frontMatter['title'] ?? ucwords(str_replace('-', ' ', $slug)),
                'excerpt' => $frontMatter['excerpt'] ?? '',
                'mtime' => $stat['mtime'],
                'size' => $stat['size']
            ];
        }
        
        // 按修改时间排序
        usort($reports, function($a, $b) {
            return $b['mtime'] <=> $a['mtime'];
        });
        
        return $limit ? array_slice($reports, 0, $limit) : $reports;
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