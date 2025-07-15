<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use League\CommonMark\CommonMarkConverter;
use App\Models\BlogComment;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    private $markdownConverter;
    
    public function __construct()
    {
        // Configure Markdown converter
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 5,
        ];
        
        $this->markdownConverter = new CommonMarkConverter($config);
    }
    
    /**
     * Display blog homepage
     */
    public function index(Request $request)
    {
        // Get search query parameter
        $searchQuery = $request->input('search');
        
        // Get all blog posts
        $allPosts = $this->getBlogPosts();
        
        // Apply search filter
        if (!empty($searchQuery)) {
            $allPosts = $this->filterPostsBySearch($allPosts, $searchQuery);
        }
        
        // Pagination settings
        $perPage = 6; // Display 6 articles per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($allPosts, ($currentPage - 1) * $perPage, $perPage);
        
        // Create paginator
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
        
        // Preserve query parameters
        $posts->appends($request->query());
        
        // Get latest posts for sidebar
        $latestPosts = array_slice($allPosts, 0, 3);
        
        // Get categories
        $categories = $this->getCategories($allPosts);
        
        return view('blog.index', compact('posts', 'latestPosts', 'categories'));
    }
    
    /**
     * Display single blog post
     */
    public function show($slug)
    {
        $posts = $this->getBlogPosts();
        $post = collect($posts)->firstWhere('slug', $slug);
        
        if (!$post) {
            abort(404, 'Article not found');
        }
        
        // Get related posts
        $relatedPosts = collect($posts)
            ->where('category', $post['category'])
            ->where('slug', '!=', $slug)
            ->take(3)
            ->toArray();
        
        // Convert content to HTML (content has already removed front matter)
        $post['html_content'] = $this->markdownConverter->convert($post['content']);
        
        // Get comments
        $comments = BlogComment::forBlog($slug)
            ->approved()
            ->latest()
            ->get();
        
        return view('blog.show', compact('post', 'relatedPosts', 'comments'));
    }
    
    /**
     * Get blog posts list
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
            
            // Process individual .md files
            $mdFiles = collect(File::glob($blogDir . '/*.md'))
                ->map(function ($file) {
                    $content = File::get($file);
                    $filename = pathinfo($file, PATHINFO_FILENAME);
                    
                    // Parse front matter metadata
                    $metadata = $this->parseMetadata($content);
                    
                    // Remove front matter and process images in content (use "shared" as folder name)
                    $contentWithoutFrontMatter = $this->removeFrontMatter($content);
                    $processedContent = $this->processBlogImages($contentWithoutFrontMatter, 'shared');
                    
                    // Process image path in YAML front matter
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
            
            // Process folder-type blog posts
            $directories = collect(File::directories($blogDir))
                ->map(function ($dir) {
                    $dirName = basename($dir);
                    $postFile = $dir . '/index.md';
                    $imagesDir = $dir . '/images';
                    
                    // Check if index.md file exists
                    if (File::exists($postFile)) {
                        $content = File::get($postFile);
                        $metadata = $this->parseMetadata($content);
                        $mtime = $this->extractModificationTime($content, $postFile);
                        
                        // Get images list
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
                        
                        // Remove front matter and process images in content
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
     * Process blog image paths
     */
    private function processBlogImages($content, $folderName)
    {
        // Process Markdown format images: ![alt](images/filename.jpg)
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
        
        // Process HTML format images: <img src="./images/filename.jpg" alt="alt text">
        $content = preg_replace_callback(
            '/<img\s+[^>]*src=["\'](?:\.\/)?(?!http)(?!\/)(images\/[^"\']+)["\'][^>]*>/i',
            function ($matches) use ($folderName) {
                $imagePath = $matches[1];
                $filename = basename($imagePath);
                $imageUrl = route('blog.image', ['folder' => $folderName, 'filename' => $filename]);
                
                // Rebuild img tag, preserve other attributes
                $imgTag = $matches[0];
                $imgTag = preg_replace('/src=["\'](?:\.\/)?(?!http)(?!\/)(images\/[^"\']+)["\']/i', 'src="' . $imageUrl . '"', $imgTag);
                
                return $imgTag;
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * Provide blog image access
     */
    public function getBlogImage($folder, $filename)
    {
        // If folder name is "shared", use shared images directory
        if ($folder === 'shared') {
            $imagePath = storage_path("blog/images/{$filename}");
        } else {
            $imagePath = storage_path("blog/{$folder}/images/{$filename}");
        }
        
        if (!File::exists($imagePath)) {
            abort(404, 'Image not found');
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
     * Parse article front matter metadata
     */
    private function parseMetadata($content)
    {
        $metadata = [];
        
        // Use more robust regex to match YAML front matter
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
     * Filter posts by search query
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
     * Get categories list
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
     * Remove front matter section
     */
    private function removeFrontMatter($content)
    {
        // More robust YAML front matter removal regex
        // Handle different line ending formats: \n, \r\n, \r
        $patterns = [
            // Standard format: --- content ---
            '/^---\s*[\r\n]+.*?[\r\n]+---\s*[\r\n]+/s',
            // More relaxed format
            '/^---.*?---\s*[\r\n]*/s',
            // Handle cases that may not have ending newlines
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
        
        // Clean leading extra empty lines and spaces
        return ltrim($cleaned, "\r\n\t ");
    }
    
    /**
     * Extract article excerpt
     */
    private function extractExcerpt($content)
    {
        // Remove front matter metadata
        $content = $this->removeFrontMatter($content);
        
        // Remove Markdown markup
        $content = preg_replace('/[#*`_\[\]()]/', '', $content);
        $content = preg_replace('/!\[.*?\]\(.*?\)/', '', $content);
        $content = preg_replace('/\[.*?\]\(.*?\)/', '', $content);
        
        // Get first 150 characters
        $excerpt = mb_substr(trim($content), 0, 150);
        
        return $excerpt ? $excerpt . '...' : 'No excerpt available';
    }
    
    /**
     * Calculate reading time
     */
    private function calculateReadingTime($content)
    {
        $wordCount = str_word_count(strip_tags($content));
        $readingTime = ceil($wordCount / 200); // Assume 200 words per minute
        
        return max(1, $readingTime);
    }
    
    /**
     * Extract modification time
     */
    private function extractModificationTime($content, $filePath)
    {
        // Try to parse date from content using more robust regex
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
        
        // If parsing fails, use file modification time
        return File::lastModified($filePath);
    }
    
    /**
     * Create comment
     */
    public function storeComment(Request $request, $slug)
    {
        // Validate blog post exists
        $posts = $this->getBlogPosts();
        $post = collect($posts)->firstWhere('slug', $slug);
        
        if (!$post) {
            return response()->json(['error' => 'Article not found'], 404);
        }
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:1|max:1000',
            'author_name' => 'nullable|string|max:50',
        ], [
            'content.required' => 'Comment content cannot be empty',
            'content.min' => 'Comment content must be at least 1 character',
            'content.max' => 'Comment content cannot exceed 1000 characters',
            'author_name.max' => 'Username cannot exceed 50 characters',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        
        // Generate commenter name
        $authorName = $request->input('author_name');
        if (empty($authorName)) {
            $authorName = BlogComment::generateRandomName();
        }
        
        // Create comment
        $comment = BlogComment::create([
            'blog_slug' => $slug,
            'author_name' => $authorName,
            'content' => $request->input('content'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_approved' => true, // Auto-approve
        ]);
        
        // Return created comment
        return response()->json([
            'success' => true,
            'message' => 'Comment posted successfully!',
            'comment' => [
                'id' => $comment->id,
                'author_name' => $comment->author_name,
                'content' => strip_tags($comment->content), // Only remove HTML tags, don't escape special characters
                'created_at' => $comment->formatted_created_at,
                'time_ago' => $comment->time_ago,
            ]
        ]);
    }
    
    /**
     * Get comments list
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
                    'content' => strip_tags($comment->content), // Only remove HTML tags, don't escape special characters
                    'created_at' => $comment->formatted_created_at,
                    'time_ago' => $comment->time_ago,
                ];
            })
        ]);
    }
} 