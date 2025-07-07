<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;
use App\Models\Category;
use App\Models\Tag;

class BlogController extends Controller
{
    private $markdownConverter;
    
    public function __construct()
    {
        $this->markdownConverter = new CommonMarkConverter();
    }

    /**
     * 显示博客文章列表
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $posts = $this->getBlogPosts($search);
        
        return view('admin.blog.index', compact('posts', 'search'));
    }

    /**
     * 显示创建博客文章表单
     */
    public function create()
    {
        $categories = Category::active()->ordered()->get();
        $tags = Tag::active()->ordered()->get();
        
        return view('admin.blog.create', compact('categories', 'tags'));
    }

    /**
     * 保存新的博客文章
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'author' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'image' => 'nullable|url',
            'published' => 'boolean',
        ], [
            'title.required' => '文章标题不能为空',
            'slug.required' => '文章别名不能为空',
            'slug.regex' => '文章别名只能包含小写字母、数字和连字符',
            'content.required' => '文章内容不能为空',
            'author.required' => '作者不能为空',
            'category_id.exists' => '选择的分类不存在',
            'tags.*.exists' => '选择的标签不存在',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $slug = $request->slug;
        $blogDir = storage_path('blog');
        
        // 确保博客目录存在
        if (!File::exists($blogDir)) {
            File::makeDirectory($blogDir, 0755, true);
        }

        // 检查slug是否已存在
        if ($this->slugExists($slug)) {
            return back()->withErrors(['slug' => '文章别名已存在'])->withInput();
        }

        // 准备文章内容
        $frontMatter = $this->prepareFrontMatter($request);
        $content = $frontMatter . "\n\n" . $request->content;

        // 保存文件
        $filePath = $blogDir . '/' . $slug . '.md';
        File::put($filePath, $content);

        // 处理标签关联
        if ($request->has('tags')) {
            $this->syncBlogTags($slug, $request->tags);
        }

        return redirect()->route('admin.blog.index')->with('success', '博客文章创建成功！');
    }

    /**
     * 显示单个博客文章
     */
    public function show($slug)
    {
        $post = $this->findPostBySlug($slug);
        
        if (!$post) {
            abort(404, '文章不存在');
        }
        
        return view('admin.blog.show', compact('post'));
    }

    /**
     * 显示编辑博客文章表单
     */
    public function edit($slug)
    {
        $post = $this->findPostBySlug($slug);
        
        if (!$post) {
            abort(404, '文章不存在');
        }

        $categories = Category::active()->ordered()->get();
        $tags = Tag::active()->ordered()->get();
        $selectedTags = $this->getBlogTags($slug);
        
        return view('admin.blog.edit', compact('post', 'categories', 'tags', 'selectedTags'));
    }

    /**
     * 更新博客文章
     */
    public function update(Request $request, $slug)
    {
        $post = $this->findPostBySlug($slug);
        
        if (!$post) {
            abort(404, '文章不存在');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'author' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'image' => 'nullable|url',
            'published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 准备文章内容
        $frontMatter = $this->prepareFrontMatter($request);
        $content = $frontMatter . "\n\n" . $request->content;

        // 更新文件
        $filePath = $this->getPostFilePath($slug);
        File::put($filePath, $content);

        // 处理标签关联
        $this->syncBlogTags($slug, $request->input('tags', []));

        return redirect()->route('admin.blog.index')->with('success', '博客文章更新成功！');
    }

    /**
     * 删除博客文章
     */
    public function destroy($slug)
    {
        $post = $this->findPostBySlug($slug);
        
        if (!$post) {
            abort(404, '文章不存在');
        }

        $filePath = $this->getPostFilePath($slug);
        
        // 删除文件
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // 删除标签关联
        $this->syncBlogTags($slug, []);

        return redirect()->route('admin.blog.index')->with('success', '博客文章删除成功！');
    }

    /**
     * 获取博客文章列表
     */
    private function getBlogPosts($search = null)
    {
        $blogDir = storage_path('blog');
        $posts = [];
        
        if (!File::exists($blogDir)) {
            return $posts;
        }

        // 处理独立的 .md 文件
        $mdFiles = File::glob($blogDir . '/*.md');
        foreach ($mdFiles as $file) {
            $post = $this->parsePostFile($file);
            if ($post && (!$search || $this->matchesSearch($post, $search))) {
                $posts[] = $post;
            }
        }

        // 处理文件夹类型的博客
        $directories = File::directories($blogDir);
        foreach ($directories as $dir) {
            $indexFile = $dir . '/index.md';
            if (File::exists($indexFile)) {
                $post = $this->parsePostFile($indexFile);
                if ($post && (!$search || $this->matchesSearch($post, $search))) {
                    $posts[] = $post;
                }
            }
        }

        // 按修改时间排序
        usort($posts, function($a, $b) {
            return $b['mtime'] - $a['mtime'];
        });

        return $posts;
    }

    /**
     * 解析博客文章文件
     */
    private function parsePostFile($filePath)
    {
        $content = File::get($filePath);
        $metadata = $this->parseMetadata($content);
        
        // 确定slug
        if (basename(dirname($filePath)) === 'blog') {
            // 独立文件
            $slug = pathinfo($filePath, PATHINFO_FILENAME);
        } else {
            // 文件夹中的index.md
            $slug = basename(dirname($filePath));
        }

        return [
            'slug' => $slug,
            'title' => $metadata['title'] ?? $slug,
            'excerpt' => $metadata['excerpt'] ?? '',
            'author' => $metadata['author'] ?? 'Admin',
            'category' => $metadata['category'] ?? '',
            'image' => $metadata['image'] ?? null,
            'published_at' => $metadata['date'] ?? File::lastModified($filePath),
            'mtime' => File::lastModified($filePath),
            'file_path' => $filePath,
            'published' => $metadata['published'] ?? true,
        ];
    }

    /**
     * 解析文章前置元数据
     */
    private function parseMetadata($content)
    {
        $metadata = [];
        
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
            $yamlContent = $matches[1];
            $lines = explode("\n", $yamlContent);
            
            foreach ($lines as $line) {
                if (preg_match('/^([^:]+):\s*(.*)$/', trim($line), $lineMatches)) {
                    $key = trim($lineMatches[1]);
                    $value = trim($lineMatches[2], '"\'');
                    
                    if ($key === 'tags' && strpos($value, '[') !== false) {
                        // 处理数组格式的标签
                        $value = json_decode($value, true) ?: [];
                    } elseif ($key === 'date') {
                        $value = strtotime($value) ?: time();
                    } elseif ($key === 'published') {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }
                    
                    $metadata[$key] = $value;
                }
            }
        }
        
        return $metadata;
    }

    /**
     * 准备前置元数据
     */
    private function prepareFrontMatter($request)
    {
        $category = null;
        if ($request->category_id) {
            $categoryModel = Category::find($request->category_id);
            $category = $categoryModel ? $categoryModel->name : null;
        }

        $frontMatter = "---\n";
        $frontMatter .= "title: \"" . $request->title . "\"\n";
        $frontMatter .= "excerpt: \"" . ($request->excerpt ?: '') . "\"\n";
        $frontMatter .= "author: \"" . $request->author . "\"\n";
        $frontMatter .= "category: \"" . ($category ?: '') . "\"\n";
        
        if ($request->image) {
            $frontMatter .= "image: \"" . $request->image . "\"\n";
        }
        
        $frontMatter .= "date: \"" . now()->format('Y-m-d H:i:s') . "\"\n";
        $frontMatter .= "published: " . ($request->boolean('published') ? 'true' : 'false') . "\n";
        $frontMatter .= "---";

        return $frontMatter;
    }

    /**
     * 检查搜索匹配
     */
    private function matchesSearch($post, $search)
    {
        $searchLower = strtolower($search);
        return strpos(strtolower($post['title']), $searchLower) !== false ||
               strpos(strtolower($post['excerpt']), $searchLower) !== false ||
               strpos(strtolower($post['author']), $searchLower) !== false;
    }

    /**
     * 检查slug是否存在
     */
    private function slugExists($slug)
    {
        $blogDir = storage_path('blog');
        $filePath = $blogDir . '/' . $slug . '.md';
        $dirPath = $blogDir . '/' . $slug;
        
        return File::exists($filePath) || File::exists($dirPath);
    }

    /**
     * 根据slug查找文章
     */
    private function findPostBySlug($slug)
    {
        $blogDir = storage_path('blog');
        $filePath = $blogDir . '/' . $slug . '.md';
        $dirPath = $blogDir . '/' . $slug . '/index.md';
        
        if (File::exists($filePath)) {
            return $this->parsePostFile($filePath);
        } elseif (File::exists($dirPath)) {
            return $this->parsePostFile($dirPath);
        }
        
        return null;
    }

    /**
     * 获取文章文件路径
     */
    private function getPostFilePath($slug)
    {
        $blogDir = storage_path('blog');
        $filePath = $blogDir . '/' . $slug . '.md';
        $dirPath = $blogDir . '/' . $slug . '/index.md';
        
        if (File::exists($filePath)) {
            return $filePath;
        } elseif (File::exists($dirPath)) {
            return $dirPath;
        }
        
        return $filePath; // 默认返回独立文件路径
    }

    /**
     * 同步博客标签
     */
    private function syncBlogTags($slug, $tagIds)
    {
        // 删除现有关联
        DB::table('blog_tag')->where('blog_slug', $slug)->delete();
        
        // 添加新关联
        if (!empty($tagIds)) {
            $data = [];
            foreach ($tagIds as $tagId) {
                $data[] = [
                    'blog_slug' => $slug,
                    'tag_id' => $tagId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('blog_tag')->insert($data);
        }
    }

    /**
     * 获取博客关联的标签
     */
    private function getBlogTags($slug)
    {
        return DB::table('blog_tag')
            ->where('blog_slug', $slug)
            ->pluck('tag_id')
            ->toArray();
    }
}
