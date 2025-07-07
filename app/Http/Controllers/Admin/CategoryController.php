<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * 显示分类列表
     */
    public function index(Request $request)
    {
        $query = Category::query();
        
        // 搜索功能
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $categories = $query->ordered()->paginate(20);
        $categories->appends($request->query());
        
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * 显示创建分类表单
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * 保存新分类
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:categories',
            'slug' => 'nullable|string|max:120|unique:categories|regex:/^[a-z0-9\-]+$/',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ], [
            'name.required' => '分类名称不能为空',
            'name.unique' => '分类名称已存在',
            'slug.unique' => '分类别名已存在',
            'slug.regex' => '分类别名只能包含小写字母、数字和连字符',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Category::create([
            'name' => $request->name,
            'slug' => $request->slug ?: \Illuminate\Support\Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => $request->sort_order ?: 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')->with('success', '分类创建成功！');
    }

    /**
     * 显示单个分类
     */
    public function show(Category $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    /**
     * 显示编辑分类表单
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * 更新分类
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:120|unique:categories,slug,' . $category->id . '|regex:/^[a-z0-9\-]+$/',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ], [
            'name.required' => '分类名称不能为空',
            'name.unique' => '分类名称已存在',
            'slug.unique' => '分类别名已存在',
            'slug.regex' => '分类别名只能包含小写字母、数字和连字符',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $category->update([
            'name' => $request->name,
            'slug' => $request->slug ?: \Illuminate\Support\Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => $request->sort_order ?: 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.categories.index')->with('success', '分类更新成功！');
    }

    /**
     * 删除分类
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', '分类删除成功！');
    }

    /**
     * 批量更新排序
     */
    public function updateOrder(Request $request)
    {
        $orders = $request->input('orders', []);
        
        foreach ($orders as $id => $order) {
            Category::where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['success' => true, 'message' => '排序更新成功']);
    }

    /**
     * 切换激活状态
     */
    public function toggleStatus(Request $request, Category $category)
    {
        $isActive = $request->boolean('is_active');
        $category->update(['is_active' => $isActive]);
        
        $status = $category->is_active ? '激活' : '禁用';
        return response()->json([
            'success' => true, 
            'message' => "分类已{$status}",
            'is_active' => $category->is_active
        ]);
    }
    
    /**
     * 移动分类排序
     */
    public function moveCategory(Request $request, Category $category)
    {
        $direction = $request->input('direction');
        $currentOrder = $category->sort_order;
        
        if ($direction === 'up') {
            // 向上移动：与上一个分类交换位置
            $prevCategory = Category::where('sort_order', '<', $currentOrder)
                ->orderBy('sort_order', 'desc')
                ->first();
            
            if ($prevCategory) {
                $category->update(['sort_order' => $prevCategory->sort_order]);
                $prevCategory->update(['sort_order' => $currentOrder]);
            }
        } elseif ($direction === 'down') {
            // 向下移动：与下一个分类交换位置
            $nextCategory = Category::where('sort_order', '>', $currentOrder)
                ->orderBy('sort_order', 'asc')
                ->first();
            
            if ($nextCategory) {
                $category->update(['sort_order' => $nextCategory->sort_order]);
                $nextCategory->update(['sort_order' => $currentOrder]);
            }
        }
        
        return response()->json(['success' => true, 'message' => '排序更新成功']);
    }
}
