<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportLock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReportLockController extends Controller
{
    /**
     * 显示报告锁定列表
     */
    public function index(Request $request)
    {
        $label = $request->input('label');
        $search = $request->input('search');
        
        $query = ReportLock::query();
        
        if ($label) {
            $query->where('label', $label);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        $reportLocks = $query->orderBy('is_enabled', 'desc')
                              ->orderBy('locked_at', 'desc')
                              ->paginate(20);
        $labels = ReportLock::getLabels();
        
        // 获取可用的报告列表（用于创建新锁定）
        $availableReports = $this->getAvailableReports();
        
        return view('admin.report-locks.index', compact('reportLocks', 'labels', 'label', 'search', 'availableReports'));
    }

    /**
     * 显示创建锁定表单
     */
    public function create()
    {
        $availableReports = $this->getAvailableReports();
        $labels = ['hackthebox', 'tryhackme', 'vulnhub', 'overthewire', 'picoctf', 'other'];
        
        return view('admin.report-locks.create', compact('availableReports', 'labels'));
    }

    /**
     * 存储新的报告锁定
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:255|unique:report_locks,slug',
            'label' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'password' => 'required|string|min:1',
            'description' => 'nullable|string|max:1000',
            'is_enabled' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            ReportLock::create([
                'slug' => $request->slug,
                'label' => $request->label,
                'title' => $request->title,
                'password' => $request->password, // 原始存储，不进行任何转义
                'description' => $request->description,
                'is_enabled' => $request->boolean('is_enabled', true)
            ]);

            return redirect()->route('admin.report-locks.index')
                ->with('success', "报告锁定 '{$request->title}' 创建成功！");
                
        } catch (\Exception $e) {
            Log::error('创建报告锁定失败: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => '创建失败，请重试。'])
                ->withInput();
        }
    }

    /**
     * 显示单个报告锁定详情
     */
    public function show(ReportLock $reportLock)
    {
        return view('admin.report-locks.show', compact('reportLock'));
    }

    /**
     * 显示编辑锁定表单
     */
    public function edit(ReportLock $reportLock)
    {
        $availableReports = $this->getAvailableReports();
        $labels = ['hackthebox', 'tryhackme', 'vulnhub', 'overthewire', 'picoctf', 'other'];
        
        return view('admin.report-locks.edit', compact('reportLock', 'availableReports', 'labels'));
    }

    /**
     * 更新报告锁定
     */
    public function update(Request $request, ReportLock $reportLock)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:255|unique:report_locks,slug,' . $reportLock->id,
            'label' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'password' => 'required|string|min:1',
            'description' => 'nullable|string|max:1000',
            'is_enabled' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $reportLock->update([
                'slug' => $request->slug,
                'label' => $request->label,
                'title' => $request->title,
                'password' => $request->password, // 原始存储，不进行任何转义
                'description' => $request->description,
                'is_enabled' => $request->boolean('is_enabled', true)
            ]);

            return redirect()->route('admin.report-locks.index')
                ->with('success', "报告锁定 '{$reportLock->title}' 更新成功！");
                
        } catch (\Exception $e) {
            Log::error('更新报告锁定失败: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => '更新失败，请重试。'])
                ->withInput();
        }
    }

    /**
     * 删除报告锁定
     */
    public function destroy(ReportLock $reportLock)
    {
        try {
            $title = $reportLock->title;
            $reportLock->delete();

            return response()->json([
                'success' => true,
                'message' => "报告锁定 '{$title}' 删除成功！"
            ]);
            
        } catch (\Exception $e) {
            Log::error('删除报告锁定失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '删除失败，请重试。'
            ], 500);
        }
    }

    /**
     * 切换锁定状态
     */
    public function toggleStatus(ReportLock $reportLock)
    {
        try {
            $reportLock->update([
                'is_enabled' => !$reportLock->is_enabled
            ]);

            $status = $reportLock->is_enabled ? '启用' : '禁用';
            return response()->json([
                'success' => true,
                'message' => "锁定状态已{$status}",
                'is_enabled' => $reportLock->is_enabled
            ]);
            
        } catch (\Exception $e) {
            Log::error('切换锁定状态失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '操作失败，请重试。'
            ], 500);
        }
    }

    /**
     * 批量操作
     */
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => '请选择要操作的项目'
            ], 400);
        }

        try {
            $count = 0;
            
            switch ($action) {
                case 'enable':
                    $count = ReportLock::whereIn('id', $ids)->update(['is_enabled' => true]);
                    $message = "已启用 {$count} 个锁定";
                    break;
                    
                case 'disable':
                    $count = ReportLock::whereIn('id', $ids)->update(['is_enabled' => false]);
                    $message = "已禁用 {$count} 个锁定";
                    break;
                    
                case 'delete':
                    $count = ReportLock::whereIn('id', $ids)->delete();
                    $message = "已删除 {$count} 个锁定";
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => '无效的操作'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('批量操作失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '操作失败，请重试。'
            ], 500);
        }
    }

    /**
     * 获取可用的报告列表
     */
    private function getAvailableReports(): array
    {
        $reports = [];
        $reportsDir = storage_path('reports');
        $hacktheboxDir = storage_path('reports/Hackthebox-Walkthrough');
        
        // 获取普通报告
        if (File::exists($reportsDir)) {
            $files = File::glob($reportsDir . '/*.md');
            foreach ($files as $file) {
                $slug = pathinfo($file, PATHINFO_FILENAME);
                $content = File::get($file);
                
                // 提取标题
                $title = $slug;
                if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                    $title = trim($matches[1]);
                }
                
                $reports[] = [
                    'slug' => $slug,
                    'title' => $title,
                    'type' => 'normal',
                    'label' => 'other'
                ];
            }
        }
        
        // 获取 HackTheBox 报告 - 支持新的难度分类结构
        if (File::exists($hacktheboxDir) && File::isDirectory($hacktheboxDir)) {
            $difficulties = ['Easy', 'Medium', 'Hard', 'Insane', 'Fortresses'];
            
            foreach ($difficulties as $difficulty) {
                $difficultyDir = $hacktheboxDir . '/' . $difficulty;
                if (File::exists($difficultyDir) && File::isDirectory($difficultyDir)) {
                    $machineDirectories = File::directories($difficultyDir);
                    foreach ($machineDirectories as $dir) {
                        $machineName = basename($dir);
                        $walkthroughFile = $dir . '/Walkthrough.md';
                        
                        if (File::exists($walkthroughFile)) {
                            $reports[] = [
                                'slug' => 'htb-' . $machineName,
                                'title' => $machineName . ' (' . $difficulty . ')',
                                'type' => 'hackthebox',
                                'label' => 'hackthebox',
                                'difficulty' => $difficulty
                            ];
                        }
                    }
                }
            }
        }
        
        return $reports;
    }
}
