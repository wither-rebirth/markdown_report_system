<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogComment;

class CommentController extends Controller
{
    /**
     * 显示评论列表
     */
    public function index(Request $request)
    {
        $query = BlogComment::latest();

        // 搜索过滤
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('author_name', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('blog_slug', 'like', "%{$search}%");
            });
        }

        // 状态过滤
        if ($status = $request->input('status')) {
            if ($status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        // 分页
        $comments = $query->paginate(20);
        $comments->appends($request->query());

        // 统计信息
        $stats = [
            'total' => BlogComment::count(),
            'approved' => BlogComment::where('is_approved', true)->count(),
            'pending' => BlogComment::where('is_approved', false)->count(),
        ];

        return view('admin.comments.index', compact('comments', 'stats'));
    }

    /**
     * 显示单个评论详情
     */
    public function show(BlogComment $comment)
    {
        return view('admin.comments.show', compact('comment'));
    }

    /**
     * 编辑评论
     */
    public function edit(BlogComment $comment)
    {
        return view('admin.comments.edit', compact('comment'));
    }

    /**
     * 更新评论
     */
    public function update(Request $request, BlogComment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'author_name' => 'required|string|max:50',
            'is_approved' => 'boolean',
        ], [
            'content.required' => '评论内容不能为空',
            'author_name.required' => '作者名称不能为空',
        ]);

        $comment->update([
            'content' => $request->content,
            'author_name' => $request->author_name,
            'is_approved' => $request->boolean('is_approved'),
        ]);

        return redirect()->route('admin.comments.index')->with('success', '评论更新成功！');
    }

    /**
     * 删除评论
     */
    public function destroy(BlogComment $comment)
    {
        $comment->delete();
        return redirect()->route('admin.comments.index')->with('success', '评论删除成功！');
    }

    /**
     * 批量操作
     */
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('comment_ids', []);

        if (empty($ids)) {
            return back()->with('error', '请选择要操作的评论');
        }

        switch ($action) {
            case 'approve':
                BlogComment::whereIn('id', $ids)->update(['is_approved' => true]);
                return back()->with('success', '评论已批量审核通过');

            case 'reject':
                BlogComment::whereIn('id', $ids)->update(['is_approved' => false]);
                return back()->with('success', '评论已批量拒绝');

            case 'delete':
                BlogComment::whereIn('id', $ids)->delete();
                return back()->with('success', '评论已批量删除');

            default:
                return back()->with('error', '无效的操作');
        }
    }

    /**
     * 快速切换审核状态
     */
    public function toggleApproval(BlogComment $comment)
    {
        $comment->update(['is_approved' => !$comment->is_approved]);
        
        $status = $comment->is_approved ? '通过' : '拒绝';
        return back()->with('success', "评论审核已{$status}");
    }

    /**
     * 根据博客文章筛选评论
     */
    public function byBlog($slug)
    {
        $comments = BlogComment::forBlog($slug)->latest()->paginate(20);
        return view('admin.comments.by-blog', compact('comments', 'slug'));
    }

    /**
     * 垃圾评论检测（简单实现）
     */
    public function detectSpam()
    {
        // 简单的垃圾评论检测规则
        $spamKeywords = ['spam', '广告', '推广', 'http://', 'https://'];
        $suspiciousComments = [];

        BlogComment::chunk(100, function ($comments) use ($spamKeywords, &$suspiciousComments) {
            foreach ($comments as $comment) {
                foreach ($spamKeywords as $keyword) {
                    if (stripos($comment->content, $keyword) !== false) {
                        $suspiciousComments[] = $comment->id;
                        break;
                    }
                }
            }
        });

        // 标记为待审核
        if (!empty($suspiciousComments)) {
            BlogComment::whereIn('id', $suspiciousComments)->update(['is_approved' => false]);
        }

        return back()->with('success', '垃圾评论检测完成，发现 ' . count($suspiciousComments) . ' 条可疑评论');
    }
}
