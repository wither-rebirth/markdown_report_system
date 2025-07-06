<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use League\CommonMark\CommonMarkConverter;
use App\BlogComment;
use Illuminate\Support\Facades\Validator;

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
        
        // 获取评论
        $comments = BlogComment::forBlog($slug)
            ->approved()
            ->latest()
            ->get();
        
        return view('blog.show', compact('post', 'relatedPosts', 'comments'));
    }
    
    /**
     * 获取博客文章列表
     */
    private function getBlogPosts()
    {
        $blogDir = storage_path('blog');
        
        if (!File::exists($blogDir)) {
            File::makeDirectory($blogDir, 0755, true);
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
     * 创建评论
     */
    public function storeComment(Request $request, $slug)
    {
        // 验证博客文章是否存在
        $posts = $this->getBlogPosts();
        $post = collect($posts)->firstWhere('slug', $slug);
        
        if (!$post) {
            return response()->json(['error' => '文章不存在'], 404);
        }
        
        // 验证输入
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:1|max:1000',
            'author_name' => 'nullable|string|max:50',
        ], [
            'content.required' => '评论内容不能为空',
            'content.min' => '评论内容至少需要1个字符',
            'content.max' => '评论内容最多1000个字符',
            'author_name.max' => '用户名最多50个字符',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        
        // 生成评论者名字
        $authorName = $request->input('author_name');
        if (empty($authorName)) {
            $authorName = BlogComment::generateRandomName();
        }
        
        // 创建评论
        $comment = BlogComment::create([
            'blog_slug' => $slug,
            'author_name' => $authorName,
            'content' => $request->input('content'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_approved' => true, // 自动审核通过
        ]);
        
        // 返回创建的评论
        return response()->json([
            'success' => true,
            'message' => '评论发表成功！',
            'comment' => [
                'id' => $comment->id,
                'author_name' => $comment->author_name,
                'content' => $comment->clean_content,
                'created_at' => $comment->formatted_created_at,
                'time_ago' => $comment->time_ago,
            ]
        ]);
    }
    
    /**
     * 获取评论列表
     */
    public function getComments($slug)
    {
        $comments = BlogComment::forBlog($slug)
            ->approved()
            ->latest()
            ->get();
        
        return response()->json([
            'comments' => $comments->map(function($comment) {
                return [
                    'id' => $comment->id,
                    'author_name' => $comment->author_name,
                    'content' => $comment->clean_content,
                    'created_at' => $comment->formatted_created_at,
                    'time_ago' => $comment->time_ago,
                ];
            })
        ]);
    }
} 