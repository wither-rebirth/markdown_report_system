<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class ReportUploadController extends Controller
{
    /**
     * 显示报告上传页面
     */
    public function index()
    {
        $reportsDir = storage_path('reports');
        
        // 获取现有报告统计
        $stats = $this->getReportStats();
        
        return view('admin.report-upload.index', compact('stats'));
    }
    
    /**
     * 上传单个Markdown文件
     */
    public function uploadMarkdown(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'markdown_file' => 'required|file|mimetypes:text/markdown,text/plain|max:10240', // 10MB
            'title' => 'nullable|string|max:255',
            'category' => 'required|string|in:general,hackthebox,vulnhub,tryhackme'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'File validation failed');
        }
        
        try {
            $file = $request->file('markdown_file');
            $category = $request->input('category');
            $customTitle = $request->input('title');
            
            // 读取文件内容
            $content = $file->get();
            
            // 生成文件名
            $fileName = $this->generateFileName($content, $file->getClientOriginalName(), $customTitle);
            
            // 确定保存路径
            $savePath = $this->getSavePath($category, $fileName);
            
            // 检查文件是否已存在
            if (File::exists($savePath)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "File {$fileName} already exists");
            }
            
            // 确保目录存在
            $directory = dirname($savePath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            
            // 保存文件
            File::put($savePath, $content);
            
            // 清除相关缓存
            $this->clearReportCache();
            
            // 自动同步报告锁定
            $this->syncReportLocks();
            
            return redirect()->back()
                ->with('success', "Report {$fileName} uploaded successfully");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 上传ZIP压缩包
     */
    public function uploadZip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zip_file' => 'required|file|mimes:zip|max:51200', // 50MB
            'category' => 'required|string|in:general,hackthebox,vulnhub,tryhackme',
            'extract_structure' => 'required|string|in:flat,preserve'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'ZIP file validation failed');
        }
        
        try {
            $file = $request->file('zip_file');
            $category = $request->input('category');
            $extractStructure = $request->input('extract_structure');
            
            // 创建临时目录
            $tempDir = storage_path('temp/' . uniqid('zip_extract_'));
            File::makeDirectory($tempDir, 0755, true);
            
            // 解压ZIP文件
            $zip = new ZipArchive;
            $res = $zip->open($file->getPathname());
            
            if ($res !== TRUE) {
                File::deleteDirectory($tempDir);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Unable to open ZIP file');
            }
            
            $zip->extractTo($tempDir);
            $zip->close();
            
            // 处理解压后的文件
            $processedFiles = $this->processExtractedFiles($tempDir, $category, $extractStructure);
            
            // 清理临时目录
            File::deleteDirectory($tempDir);
            
            // 清除相关缓存
            $this->clearReportCache();
            
            // 自动同步报告锁定
            $this->syncReportLocks();
            
            return redirect()->back()
                ->with('success', "Successfully processed {$processedFiles} files");
                
        } catch (\Exception $e) {
            // 确保清理临时目录
            if (isset($tempDir) && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'ZIP processing failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取报告统计信息
     */
    private function getReportStats()
    {
        $stats = [
            'total' => 0,
            'general' => 0,
            'hackthebox' => 0,
            'vulnhub' => 0,
            'tryhackme' => 0
        ];
        
        $reportsDir = storage_path('reports');
        if (!File::exists($reportsDir)) {
            return $stats;
        }
        
        // 统计一般报告（根目录的.md文件）
        $generalFiles = File::glob($reportsDir . '/*.md');
        $stats['general'] = count($generalFiles);
        
        // 统计HackTheBox报告
        $hacktheboxDir = $reportsDir . '/Hackthebox-Walkthrough';
        if (File::exists($hacktheboxDir)) {
            $htbCount = 0;
            $difficulties = ['Easy', 'Medium', 'Hard', 'Insane', 'Fortresses', 'Prolabs'];
            foreach ($difficulties as $difficulty) {
                $diffDir = $hacktheboxDir . '/' . $difficulty;
                if (File::exists($diffDir)) {
                    $machines = File::directories($diffDir);
                    foreach ($machines as $machine) {
                        if (File::exists($machine . '/Walkthrough.md')) {
                            $htbCount++;
                        }
                    }
                }
            }
            $stats['hackthebox'] = $htbCount;
        }
        
        // 统计VulnHub报告
        $vulnhubDir = $reportsDir . '/Vulnerhub';
        if (File::exists($vulnhubDir)) {
            $vulnhubCount = 0;
            // 目录结构的报告
            $machines = File::directories($vulnhubDir);
            foreach ($machines as $machine) {
                if (File::exists($machine . '/Walkthrough.md')) {
                    $vulnhubCount++;
                }
            }
            // 直接的.md文件
            $directFiles = File::glob($vulnhubDir . '/*.md');
            $vulnhubCount += count($directFiles);
            $stats['vulnhub'] = $vulnhubCount;
        }
        
        // 统计TryHackMe报告
        $tryhackmeDir = $reportsDir . '/TryHackMe';
        if (File::exists($tryhackmeDir)) {
            $rooms = File::directories($tryhackmeDir);
            $stats['tryhackme'] = count($rooms);
        }
        
        $stats['total'] = $stats['general'] + $stats['hackthebox'] + $stats['vulnhub'] + $stats['tryhackme'];
        
        return $stats;
    }
    
    /**
     * 生成文件名
     */
    private function generateFileName($content, $originalName, $customTitle = null)
    {
        // 如果提供了自定义标题，使用它
        if (!empty($customTitle)) {
            $slug = Str::slug($customTitle);
            if (!empty($slug)) {
                return $slug . '.md';
            }
        }
        
        // 尝试从内容提取标题
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = trim($matches[1]);
            $slug = Str::slug($title);
            if (!empty($slug)) {
                return $slug . '.md';
            }
        }
        
        // 使用原始文件名
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $slug = Str::slug($filename);
        if (!empty($slug)) {
            return $slug . '.md';
        }
        
        // 最后使用时间戳
        return 'report-' . now()->format('YmdHis') . '.md';
    }
    
    /**
     * 获取保存路径
     */
    private function getSavePath($category, $fileName)
    {
        $reportsDir = storage_path('reports');
        
        switch ($category) {
            case 'general':
                return $reportsDir . '/' . $fileName;
                
            case 'hackthebox':
                // 对于HackTheBox，需要根据机器名创建目录结构
                $machineName = pathinfo($fileName, PATHINFO_FILENAME);
                $difficulty = 'Medium'; // 默认难度，用户可以后续移动
                return $reportsDir . '/Hackthebox-Walkthrough/' . $difficulty . '/' . $machineName . '/Walkthrough.md';
                
            case 'vulnhub':
                // 对于VulnHub，创建机器目录
                $machineName = pathinfo($fileName, PATHINFO_FILENAME);
                return $reportsDir . '/Vulnerhub/' . $machineName . '/Walkthrough.md';
                
            case 'tryhackme':
                // 对于TryHackMe，创建房间目录
                $roomName = pathinfo($fileName, PATHINFO_FILENAME);
                return $reportsDir . '/TryHackMe/' . $roomName . '/Walkthrough.md';
                
            default:
                return $reportsDir . '/' . $fileName;
        }
    }
    
    /**
     * 处理解压后的文件
     */
    private function processExtractedFiles($tempDir, $category, $extractStructure)
    {
        $processedCount = 0;
        $markdownFiles = File::allFiles($tempDir);
        
        foreach ($markdownFiles as $file) {
            // 只处理Markdown文件
            if (!in_array($file->getExtension(), ['md', 'markdown'])) {
                continue;
            }
            
            $content = File::get($file->getPathname());
            $relativePath = $file->getRelativePathname();
            
            if ($extractStructure === 'flat') {
                // 平铺模式：所有文件放在分类目录下
                $fileName = $this->generateFileName($content, $file->getFilename());
                $savePath = $this->getSavePath($category, $fileName);
            } else {
                // 保持结构模式：尝试保持原有目录结构
                $savePath = $this->getStructuredSavePath($tempDir, $file, $category);
            }
            
            // 确保目录存在
            $directory = dirname($savePath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            
            // 检查文件是否已存在
            if (!File::exists($savePath)) {
                // 处理图片文件（如果存在）
                $this->processImages($file, $savePath);
                
                // 保存Markdown文件
                File::put($savePath, $content);
                $processedCount++;
            }
        }
        
        return $processedCount;
    }
    
    /**
     * 获取结构化保存路径
     */
    private function getStructuredSavePath($tempDir, $file, $category)
    {
        $reportsDir = storage_path('reports');
        $relativePath = $file->getRelativePathname();
        $pathParts = explode('/', dirname($relativePath));
        
        switch ($category) {
            case 'hackthebox':
                // 尝试识别HackTheBox结构
                $machineName = $pathParts[0] ?? pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $difficulty = $this->detectDifficulty($pathParts) ?: 'Medium';
                return $reportsDir . '/Hackthebox-Walkthrough/' . $difficulty . '/' . $machineName . '/Walkthrough.md';
                
            case 'vulnhub':
                $machineName = $pathParts[0] ?? pathinfo($file->getFilename(), PATHINFO_FILENAME);
                return $reportsDir . '/Vulnerhub/' . $machineName . '/Walkthrough.md';
                
            case 'tryhackme':
                $roomName = $pathParts[0] ?? pathinfo($file->getFilename(), PATHINFO_FILENAME);
                return $reportsDir . '/TryHackMe/' . $roomName . '/Walkthrough.md';
                
            default:
                return $reportsDir . '/' . $file->getFilename();
        }
    }
    
    /**
     * 检测难度级别
     */
    private function detectDifficulty($pathParts)
    {
        $difficulties = ['Easy', 'Medium', 'Hard', 'Insane', 'Fortresses', 'Prolabs'];
        
        foreach ($pathParts as $part) {
            if (in_array($part, $difficulties)) {
                return $part;
            }
        }
        
        return null;
    }
    
    /**
     * 处理图片文件
     */
    private function processImages($markdownFile, $savePath)
    {
        $sourceDir = dirname($markdownFile->getPathname());
        $targetDir = dirname($savePath);
        $imagesSourceDir = $sourceDir . '/images';
        $imagesTargetDir = $targetDir . '/images';
        
        // 如果存在images目录，复制到目标位置
        if (File::exists($imagesSourceDir) && File::isDirectory($imagesSourceDir)) {
            if (!File::exists($imagesTargetDir)) {
                File::makeDirectory($imagesTargetDir, 0755, true);
            }
            
            $imageFiles = File::allFiles($imagesSourceDir);
            foreach ($imageFiles as $imageFile) {
                $targetImagePath = $imagesTargetDir . '/' . $imageFile->getFilename();
                if (!File::exists($targetImagePath)) {
                    File::copy($imageFile->getPathname(), $targetImagePath);
                }
            }
        }
    }
    
    /**
     * 清除报告缓存
     */
    private function clearReportCache()
    {
        // 清除所有报告相关缓存
        Cache::flush();
    }
    
    /**
     * 自动同步报告锁定
     */
    private function syncReportLocks()
    {
        try {
            // 调用 reports:sync-locks 命令
            Artisan::call('reports:sync-locks');
            
            // 记录日志（可选）
            Log::info('Report locks synced after upload');
        } catch (\Exception $e) {
            // 如果同步失败，记录错误但不影响上传流程
            Log::error('Failed to sync report locks: ' . $e->getMessage());
        }
    }
}