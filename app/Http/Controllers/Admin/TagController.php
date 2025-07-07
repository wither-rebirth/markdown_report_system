<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tag;

class TagController extends Controller
{
    /**
     * 显示标签列表
     */
    public function index(Request $request)
    {
        $query = Tag::query();
        
        // 搜索功能
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        
        $tags = $query->ordered()->paginate(20);
        $tags->appends($request->query());
        
        return view('admin.tags.index', compact('tags'));
    }

    /**
     * 显示创建标签表单
     */
    public function create()
    {
        return view('admin.tags.create');
    }

    /**
     * 保存新标签
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:tags',
            'slug' => 'nullable|string|max:120|unique:tags|regex:/^[a-z0-9\-]+$/',
            'color' => 'nullable|string|max:7|regex:/^#[0-9a-fA-F]{6}$/',
            'is_active' => 'boolean',
        ], [
            'name.required' => '标签名称不能为空',
            'name.unique' => '标签名称已存在',
            'slug.unique' => '标签别名已存在',
            'slug.regex' => '标签别名只能包含小写字母、数字和连字符',
            'color.regex' => '颜色值必须为有效的十六进制颜色代码',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Tag::create([
            'name' => $request->name,
            'slug' => $request->slug ?: \Illuminate\Support\Str::slug($request->name),
            'color' => $request->color,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.tags.index')->with('success', '标签创建成功！');
    }

    /**
     * 显示单个标签
     */
    public function show(Tag $tag)
    {
        return view('admin.tags.show', compact('tag'));
    }

    /**
     * 显示编辑标签表单
     */
    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * 更新标签
     */
    public function update(Request $request, Tag $tag)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:tags,name,' . $tag->id,
            'slug' => 'nullable|string|max:120|unique:tags,slug,' . $tag->id . '|regex:/^[a-z0-9\-]+$/',
            'color' => 'nullable|string|max:7|regex:/^#[0-9a-fA-F]{6}$/',
            'is_active' => 'boolean',
        ], [
            'name.required' => '标签名称不能为空',
            'name.unique' => '标签名称已存在',
            'slug.unique' => '标签别名已存在',
            'slug.regex' => '标签别名只能包含小写字母、数字和连字符',
            'color.regex' => '颜色值必须为有效的十六进制颜色代码',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $tag->update([
            'name' => $request->name,
            'slug' => $request->slug ?: \Illuminate\Support\Str::slug($request->name),
            'color' => $request->color,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.tags.index')->with('success', '标签更新成功！');
    }

    /**
     * 删除标签
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();
        return redirect()->route('admin.tags.index')->with('success', '标签删除成功！');
    }

    /**
     * 切换激活状态
     */
    public function toggleStatus(Tag $tag)
    {
        $tag->update(['is_active' => !$tag->is_active]);
        
        $status = $tag->is_active ? '激活' : '禁用';
        return back()->with('success', "标签已{$status}");
    }

    /**
     * 批量删除标签
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return back()->with('error', '请选择要删除的标签');
        }

        Tag::whereIn('id', $ids)->delete();
        
        return back()->with('success', '批量删除成功');
    }
}
