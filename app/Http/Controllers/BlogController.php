<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use League\CommonMark\CommonMarkConverter;

class BlogController extends Controller
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
     * 显示博客首页
     */
    public function index(Request $request)
    {
        // 获取搜索查询参数
        $searchQuery = $request->input('search');
        
        // 获取所有博客文章
        $allPosts = $this->getBlogPosts();
        
        // 应用搜索过滤
        if (!empty($searchQuery)) {
            $allPosts = $this->filterPostsBySearch($allPosts, $searchQuery);
        }
        
        // 分页设置
        $perPage = 6; // 每页显示6篇文章
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($allPosts, ($currentPage - 1) * $perPage, $perPage);
        
        // 创建分页器
        $posts = new LengthAwarePaginator(
            $currentItems,
            count($allPosts),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        
        // 保留查询参数
        $posts->appends($request->query());
        
        // 获取最新文章用于侧边栏
        $latestPosts = array_slice($allPosts, 0, 3);
        
        // 获取分类
        $categories = $this->getCategories($allPosts);
        
        return view('blog.index', compact('posts', 'latestPosts', 'categories'));
    }
    
    /**
     * 显示单个博客文章
     */
    public function show($slug)
    {
        $posts = $this->getBlogPosts();
        $post = collect($posts)->firstWhere('slug', $slug);
        
        if (!$post) {
            abort(404, '文章不存在');
        }
        
        // 获取相关文章
        $relatedPosts = collect($posts)
            ->where('category', $post['category'])
            ->where('slug', '!=', $slug)
            ->take(3)
            ->toArray();
        
        // 转换内容为HTML（内容已经移除了前言部分）
        $post['html_content'] = $this->markdownConverter->convert($post['content']);
        
        return view('blog.show', compact('post', 'relatedPosts'));
    }
    
    /**
     * 获取博客文章列表
     */
    private function getBlogPosts()
    {
        $blogDir = storage_path('blog');
        
        if (!File::exists($blogDir)) {
            File::makeDirectory($blogDir, 0755, true);
            $this->createSamplePosts($blogDir);
        }
        
        $cacheKey = 'blog_posts_' . filemtime($blogDir);
        
        return Cache::remember($cacheKey, 600, function () use ($blogDir) {
            $posts = collect();
            
            // 处理单独的.md文件
            $mdFiles = collect(File::glob($blogDir . '/*.md'))
                ->map(function ($file) {
                    $content = File::get($file);
                    $filename = pathinfo($file, PATHINFO_FILENAME);
                    
                    // 解析前置元数据
                    $metadata = $this->parseMetadata($content);
                    
                    // 移除前言并处理内容中的图片路径（使用"shared"作为文件夹名）
                    $contentWithoutFrontMatter = $this->removeFrontMatter($content);
                    $processedContent = $this->processBlogImages($contentWithoutFrontMatter, 'shared');
                    
                    // 处理YAML前言中的图片路径
                    $processedImage = $metadata['image'] ?? null;
                    if ($processedImage && preg_match('/^\.\/images\/(.+)$/', $processedImage, $matches)) {
                        $processedImage = route('blog.image', ['folder' => 'shared', 'filename' => $matches[1]]);
                    }
                    
                    return [
                        'slug' => $filename,
                        'title' => $metadata['title'] ?? $filename,
                        'excerpt' => $metadata['excerpt'] ?? $this->extractExcerpt($content),
                        'content' => $processedContent,
                        'author' => $metadata['author'] ?? 'Admin',
                        'category' => $metadata['category'] ?? 'Technology',
                        'tags' => $metadata['tags'] ?? [],
                        'image' => $processedImage,
                        'published_at' => $metadata['date'] ?? File::lastModified($file),
                        'mtime' => File::lastModified($file),
                        'reading_time' => $this->calculateReadingTime($content),
                        'type' => 'file',
                        'images' => [],
                    ];
                });
            
            // 处理文件夹类型的blog文章
            $directories = collect(File::directories($blogDir))
                ->map(function ($dir) {
                    $dirName = basename($dir);
                    $postFile = $dir . '/index.md';
                    $imagesDir = $dir . '/images';
                    
                    // 检查是否存在index.md文件
                    if (File::exists($postFile)) {
                        $content = File::get($postFile);
                        $metadata = $this->parseMetadata($content);
                        $mtime = $this->extractModificationTime($content, $postFile);
                        
                        // 获取图片列表
                        $images = [];
                        if (File::exists($imagesDir) && File::isDirectory($imagesDir)) {
                            $imageFiles = File::glob($imagesDir . '/*.{jpg,jpeg,png,gif,bmp,webp,svg}', GLOB_BRACE);
                            $images = collect($imageFiles)->map(function ($imagePath) use ($dirName) {
                                $imageName = basename($imagePath);
                                return [
                                    'name' => $imageName,
                                    'path' => $imagePath,
                                    'url' => route('blog.image', ['folder' => $dirName, 'filename' => $imageName]),
                                ];
                            })->toArray();
                        }
                        
                        // 移除前言并处理内容中的图片路径
                        $contentWithoutFrontMatter = $this->removeFrontMatter($content);
                        $processedContent = $this->processBlogImages($contentWithoutFrontMatter, $dirName);
                        
                        return [
                            'slug' => $dirName,
                            'title' => $metadata['title'] ?? $dirName,
                            'excerpt' => $metadata['excerpt'] ?? $this->extractExcerpt($content),
                            'content' => $processedContent,
                            'author' => $metadata['author'] ?? 'Admin',
                            'category' => $metadata['category'] ?? 'Technology',
                            'tags' => $metadata['tags'] ?? [],
                            'image' => $metadata['image'] ?? (count($images) > 0 ? $images[0]['url'] : null),
                            'published_at' => $metadata['date'] ?? $mtime,
                            'mtime' => $mtime,
                            'reading_time' => $this->calculateReadingTime($content),
                            'type' => 'folder',
                            'images' => $images,
                            'folder_name' => $dirName,
                        ];
                    }
                    
                    return null;
                })
                ->filter()
                ->values();
            
            return $posts->merge($mdFiles)->merge($directories)
                ->sortByDesc('published_at')
                ->values()
                ->toArray();
        });
    }
    
    /**
     * 处理博客图片路径
     */
    private function processBlogImages($content, $folderName)
    {
        // 处理Markdown格式的图片：![alt](images/filename.jpg)
        $content = preg_replace_callback(
            '/!\[([^\]]*)\]\((?!http)(?!\/)(images\/[^)]+)\)/i',
            function ($matches) use ($folderName) {
                $altText = $matches[1];
                $imagePath = $matches[2];
                $filename = basename($imagePath);
                $imageUrl = route('blog.image', ['folder' => $folderName, 'filename' => $filename]);
                return "![{$altText}]({$imageUrl})";
            },
            $content
        );
        
        // 处理HTML格式的图片：<img src="./images/filename.jpg" alt="alt text">
        $content = preg_replace_callback(
            '/<img\s+[^>]*src=["\'](?:\.\/)?(?!http)(?!\/)(images\/[^"\']+)["\'][^>]*>/i',
            function ($matches) use ($folderName) {
                $imagePath = $matches[1];
                $filename = basename($imagePath);
                $imageUrl = route('blog.image', ['folder' => $folderName, 'filename' => $filename]);
                
                // 重新构建img标签，保持其他属性
                $imgTag = $matches[0];
                $imgTag = preg_replace('/src=["\'](?:\.\/)?(?!http)(?!\/)(images\/[^"\']+)["\']/i', 'src="' . $imageUrl . '"', $imgTag);
                
                return $imgTag;
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * 提供博客图片访问
     */
    public function getBlogImage($folder, $filename)
    {
        // 如果文件夹名为"shared"，使用共享图片目录
        if ($folder === 'shared') {
            $imagePath = storage_path("blog/images/{$filename}");
        } else {
            $imagePath = storage_path("blog/{$folder}/images/{$filename}");
        }
        
        if (!File::exists($imagePath)) {
            abort(404, '图片不存在');
        }
        
        $mimeType = File::mimeType($imagePath);
        $headers = [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ];
        
        return response()->file($imagePath, $headers);
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
     * 根据搜索查询过滤文章
     */
    private function filterPostsBySearch($posts, $searchQuery)
    {
        $searchQuery = mb_strtolower(trim($searchQuery));
        if (empty($searchQuery)) {
            return $posts;
        }
        
        return collect($posts)->filter(function ($post) use ($searchQuery) {
            $title = mb_strtolower($post['title']);
            $excerpt = mb_strtolower($post['excerpt'] ?? '');
            $content = mb_strtolower($post['content'] ?? '');
            $category = mb_strtolower($post['category'] ?? '');
            
            return mb_strpos($title, $searchQuery) !== false ||
                   mb_strpos($excerpt, $searchQuery) !== false ||
                   mb_strpos($content, $searchQuery) !== false ||
                   mb_strpos($category, $searchQuery) !== false;
        })->values()->toArray();
    }
    
    /**
     * 获取分类列表
     */
    private function getCategories($posts)
    {
        $categories = collect($posts)
            ->pluck('category')
            ->countBy()
            ->map(function ($count, $category) {
                return [
                    'name' => $category,
                    'count' => $count,
                    'slug' => str_replace(' ', '-', strtolower($category))
                ];
            })
            ->sortByDesc('count')
            ->toArray();
        
        return $categories;
    }
    
    /**
     * 移除前言部分
     */
    private function removeFrontMatter($content)
    {
        // 更健壮的YAML前言移除正则表达式
        // 处理不同的换行符格式：\n, \r\n, \r
        $patterns = [
            // 标准格式：--- 内容 ---
            '/^---\s*[\r\n]+.*?[\r\n]+---\s*[\r\n]+/s',
            // 更宽松的格式
            '/^---.*?---\s*[\r\n]*/s',
            // 处理可能没有结尾换行的情况
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
        
        // 清理开头的多余空行和空格
        return ltrim($cleaned, "\r\n\t ");
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
     * 计算阅读时间
     */
    private function calculateReadingTime($content)
    {
        $wordCount = str_word_count(strip_tags($content));
        $readingTime = ceil($wordCount / 200); // 假设每分钟阅读200个单词
        
        return max(1, $readingTime);
    }
    
    /**
     * 提取修改时间
     */
    private function extractModificationTime($content, $filePath)
    {
        // 尝试从内容中解析日期，使用更健壮的正则表达式
        $patterns = [
            '/^---\s*[\r\n]+.*?date:\s*([^\r\n]+)[\r\n]+.*?[\r\n]+---\s*[\r\n]+/s',
            '/^---.*?[\r\n]+.*?date:\s*([^\r\n]+)[\r\n]+.*?[\r\n]+---\s*[\r\n]*/s',
            '/^---.*?date:\s*([^\r\n]+).*?---/s'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $dateStr = trim($matches[1], '"\'');
                $timestamp = strtotime($dateStr);
                if ($timestamp !== false) {
                    return $timestamp;
                }
            }
        }
        
        // 如果解析失败，使用文件修改时间
        return File::lastModified($filePath);
    }
    
    /**
     * 创建示例文章
     */
    private function createSamplePosts($blogDir)
    {
        // 创建文件夹类型的示例文章
        $folderPostDir = $blogDir . '/advanced-penetration-testing';
        File::makeDirectory($folderPostDir, 0755, true);
        File::makeDirectory($folderPostDir . '/images', 0755, true);
        
        // 创建index.md文件
        $folderPostContent = '---
title: 高级渗透测试技术详解
excerpt: 详细介绍高级渗透测试技术，包含实际操作截图和工具使用示例。
author: Admin
category: 渗透测试
tags: 渗透测试, 高级技术, 实战, 截图演示
date: ' . date('Y-m-d H:i:s', strtotime('-3 days')) . '
image: /blog-images/advanced-penetration-testing/cover.jpg
---

# 高级渗透测试技术详解

在这篇文章中，我将详细介绍一些高级的渗透测试技术，并通过实际的截图来演示具体的操作过程。

## 环境准备

首先，我们需要准备渗透测试环境。以下是我的实验室环境设置：

![实验室环境](images/lab-environment.png)

如上图所示，我们的测试环境包括：
- 攻击机：Kali Linux 2023.4
- 目标机：Windows Server 2019
- 网络环境：隔离的虚拟网络

## 信息收集阶段

### 端口扫描

使用Nmap进行全面的端口扫描：

```bash
nmap -sS -sV -O -A target.example.com
```

扫描结果如下图所示：

![Nmap扫描结果](images/nmap-scan.png)

### 服务枚举

根据扫描结果，我们发现了几个关键服务：

1. **Web服务 (端口80/443)**
   - 运行Apache 2.4.41
   - 发现了管理面板登录页面

![Web服务发现](images/web-discovery.png)

2. **SMB服务 (端口445)**
   - Windows SMB 3.1.1
   - 允许匿名访问部分共享

## 漏洞利用

### Web应用漏洞

通过目录爆破，我们发现了一个未授权的管理接口：

![目录爆破结果](images/directory-bruteforce.png)

进一步分析发现存在SQL注入漏洞：

```sql
-- 注入payload
\' UNION SELECT 1,user(),database(),version()--
```

![SQL注入测试](images/sql-injection.png)

### 提权过程

成功获取Web Shell后，我们需要进行权限提升：

![Webshell获取](images/webshell.png)

使用以下PowerShell脚本进行提权：

```powershell
# 检查系统权限
whoami /priv

# 搜索可利用的服务
Get-Service | Where-Object {$_.Status -eq "Running"}
```

最终成功提权至SYSTEM权限：

![权限提升成功](images/privilege-escalation.png)

## 后渗透阶段

### 数据收集

收集敏感信息和凭据：

![数据收集](images/data-collection.png)

### 持久化

建立持久化后门以维持访问：

![持久化后门](images/persistence.png)

## 防护建议

基于本次渗透测试，我们提出以下安全建议：

1. **及时更新系统补丁**
2. **加强Web应用安全**
3. **实施最小权限原则**
4. **部署入侵检测系统**

## 总结

通过本次高级渗透测试，我们展示了完整的攻击链路，从信息收集到最终的权限获取。这些技术在实际的安全测试中非常有用。

**注意**：本文内容仅供学习和合法的安全测试使用，请勿用于非法用途。

---

*更多渗透测试技术请关注wither\'s blog，也欢迎查看我的[靶场报告](/reports)获取更多实战案例。*';

        File::put($folderPostDir . '/index.md', $folderPostContent);
        
        // 创建一些示例图片文件夹（这里只创建占位符，实际使用时用户会添加真实图片）
        $imageFiles = [
            'cover.jpg',
            'lab-environment.png', 
            'nmap-scan.png',
            'web-discovery.png',
            'directory-bruteforce.png',
            'sql-injection.png',
            'webshell.png',
            'privilege-escalation.png',
            'data-collection.png',
            'persistence.png'
        ];
        
        foreach ($imageFiles as $imageFile) {
            // 创建占位符文件，用户可以替换为真实图片
            File::put($folderPostDir . '/images/' . $imageFile, '');
        }
        
        $samplePosts = [
            [
                'filename' => 'welcome-to-my-blog.md',
                'content' => '---
title: 欢迎来到wither\'s blog
excerpt: 这是我的第一篇博客文章，介绍了博客的主要内容和方向。
author: Admin
category: 公告
tags: 博客, 欢迎, 介绍
date: ' . date('Y-m-d H:i:s') . '
image: /images/welcome.jpg
---

# 欢迎来到wither\'s blog

欢迎来到我的个人博客！这里我将分享关于网络安全、技术学习和日常思考的内容。

## 博客内容

### 🔐 网络安全
- 靶场Writeup分享
- 漏洞分析与复现
- 安全工具使用心得
- CTF比赛经验

### 💻 技术学习
- 编程语言学习笔记
- 新技术探索
- 项目开发记录
- 问题解决方案

### 🎯 靶场报告
特别推荐查看我的[靶场报告](/reports)，这里收录了我在各个靶场的详细Writeup：

- HackTheBox机器攻略
- VulnHub挑战解析
- TryHackMe学习记录
- 自建靶场测试

## 关于我

我是一名网络安全爱好者，专注于渗透测试和漏洞挖掘。通过这个博客，我希望能够记录自己的学习过程，同时与大家分享有价值的技术内容。

## 联系方式

如果您对我的文章有任何疑问或建议，欢迎通过以下方式联系我：

- 邮箱：admin@example.com
- GitHub：@admin
- Twitter：@admin

感谢您的访问，希望我的内容对您有所帮助！'
            ],
            [
                'filename' => 'web-security-basics.md',
                'content' => '---
title: Web安全基础知识梳理
excerpt: 本文整理了Web安全领域的基础知识，包括常见漏洞类型、攻击方式和防护措施。
author: Admin
category: 网络安全
tags: Web安全, 基础知识, 漏洞, 防护
date: ' . date('Y-m-d H:i:s', strtotime('-1 day')) . '
image: /images/web-security.jpg
---

# Web安全基础知识梳理

Web安全是信息安全领域的重要分支，本文将系统性地梳理Web安全的基础知识。

## OWASP Top 10

### 1. 注入攻击（Injection）
- SQL注入
- NoSQL注入
- 命令注入
- LDAP注入

### 2. 失效的身份认证（Broken Authentication）
- 弱密码策略
- 会话管理缺陷
- 多因素认证绕过

### 3. 敏感数据泄露（Sensitive Data Exposure）
- 传输过程中的数据泄露
- 存储过程中的数据泄露
- 数据库安全配置

### 4. XML外部实体攻击（XXE）
- XML外部实体引用
- 文件读取攻击
- SSRF攻击

### 5. 失效的访问控制（Broken Access Control）
- 权限提升
- 越权访问
- 目录遍历

## 常见攻击手段

### 跨站脚本攻击（XSS）
```javascript
// 反射型XSS示例
<script>alert("XSS")</script>

// 存储型XSS示例
<img src="x" onerror="alert(document.cookie)">
```

### 跨站请求伪造（CSRF）
```html
<!-- CSRF攻击示例 -->
<img src="http://bank.com/transfer?amount=1000&to=attacker">
```

### SQL注入
```sql
-- 经典SQL注入
SELECT * FROM users WHERE username = \'" OR 1=1 --\' AND password = \'123456\'
```

## 防护措施

### 输入验证
- 白名单过滤
- 参数化查询
- 编码转义

### 访问控制
- 最小权限原则
- 多层防护
- 定期权限审计

### 安全配置
- 服务器加固
- 数据库安全配置
- 网络安全配置

## 学习资源推荐

1. **在线靶场**
   - [HackTheBox](https://www.hackthebox.eu/)
   - [VulnHub](https://www.vulnhub.com/)
   - [TryHackMe](https://tryhackme.com/)

2. **学习平台**
   - [PortSwigger Web Security Academy](https://portswigger.net/web-security)
   - [OWASP WebGoat](https://owasp.org/www-project-webgoat/)

3. **工具推荐**
   - Burp Suite
   - OWASP ZAP
   - Nmap
   - Metasploit

希望这篇文章能帮助大家建立Web安全的基础知识框架！'
            ],
            [
                'filename' => 'hackthebox-writeup-template.md',
                'content' => '---
title: HackTheBox Writeup 模板
excerpt: 分享我在编写HTB Writeup时使用的标准模板，包含信息收集、漏洞利用、提权等完整流程。
author: Admin
category: 靶场攻略
tags: HackTheBox, Writeup, 模板, 渗透测试
date: ' . date('Y-m-d H:i:s', strtotime('-2 days')) . '
image: /images/htb-logo.png
---

# HackTheBox Writeup 模板

作为一名经常在HackTheBox平台练习的安全爱好者，我总结了一套标准的Writeup模板，希望能帮助大家更好地记录和分享自己的学习过程。

## 机器信息

| 属性 | 值 |
|------|-----|
| 机器名称 | Machine Name |
| 操作系统 | Linux/Windows |
| 难度等级 | Easy/Medium/Hard |
| 发布日期 | YYYY-MM-DD |
| 退役日期 | YYYY-MM-DD |

## 信息收集

### 端口扫描
```bash
# Nmap扫描
nmap -sC -sV -O -oA nmap/initial $TARGET

# 全端口扫描
nmap -p- --max-retries 1 --max-rate 500 --max-scan-delay 20 -T4 -v $TARGET
```

### 服务枚举
根据开放端口进行针对性的服务枚举：

#### HTTP/HTTPS服务
```bash
# 目录枚举
gobuster dir -u http://$TARGET -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt

# 子域名枚举
gobuster dns -d $TARGET -w /usr/share/wordlists/dnsmap.txt
```

#### SMB服务
```bash
# SMB枚举
smbclient -L //$TARGET -N
enum4linux -a $TARGET
```

## 漏洞利用

### 初始立足点
详细描述发现的漏洞及利用过程：

1. **漏洞类型**：描述漏洞类型
2. **漏洞原理**：解释漏洞的技术原理
3. **利用过程**：详细的利用步骤
4. **利用代码**：相关的exploit代码

### 获取Shell
```bash
# 反向Shell示例
nc -lvnp 4444
```

## 权限提升

### 本地信息收集
```bash
# 系统信息
uname -a
cat /etc/os-release

# 用户信息
whoami
id
sudo -l

# 网络信息
netstat -antp
ss -antp
```

### 提权方法
描述具体的提权方法和过程。

## 后渗透

### 持久化
如果需要，描述持久化方法。

### 痕迹清理
描述清理痕迹的方法。

## 总结

总结本次渗透测试的关键点：

1. **主要漏洞**：列出关键漏洞
2. **学习要点**：记录学到的新知识
3. **工具使用**：记录使用的工具和技巧
4. **防护建议**：提出安全防护建议

## 参考资料

- [HackTheBox官方](https://www.hackthebox.eu/)
- [相关CVE]()
- [参考文章]()

---

这个模板可以根据具体情况进行调整，关键是要保持结构清晰，便于他人理解和学习。更多完整的Writeup请访问我的[靶场报告](/reports)！'
            ]
        ];
        
        foreach ($samplePosts as $post) {
            File::put($blogDir . '/' . $post['filename'], $post['content']);
        }
    }
} 