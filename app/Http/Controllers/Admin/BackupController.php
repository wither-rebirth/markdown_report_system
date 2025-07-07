<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;
use Carbon\Carbon;

class BackupController extends Controller
{
    /**
     * 显示备份管理页面
     */
    public function index()
    {
        // 获取现有备份列表
        $backups = $this->getBackupList();
        
        // 获取备份统计信息
        $stats = $this->getBackupStats();
        
        return view('admin.backup.index', compact('backups', 'stats'));
    }

    /**
     * 创建数据库备份
     */
    public function createDatabaseBackup(Request $request)
    {
        try {
            $filename = 'database_backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups');
            
            // 确保备份目录存在
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }
            
            $filePath = $backupPath . '/' . $filename;
            
            // 获取数据库配置
            $dbConfig = config('database.connections.' . config('database.default'));
            $dbName = $dbConfig['database'];
            $dbUser = $dbConfig['username'];
            $dbPassword = $dbConfig['password'];
            $dbHost = $dbConfig['host'];
            
            // 构建mysqldump命令
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg($dbUser),
                escapeshellarg($dbPassword),
                escapeshellarg($dbHost),
                escapeshellarg($dbName),
                escapeshellarg($filePath)
            );
            
            // 执行备份命令
            $result = null;
            $output = [];
            exec($command, $output, $result);
            
            if ($result === 0 && File::exists($filePath)) {
                return response()->json([
                    'success' => true,
                    'message' => '数据库备份创建成功',
                    'filename' => $filename,
                    'size' => $this->formatFileSize(File::size($filePath))
                ]);
            } else {
                // 如果mysqldump不可用，使用Laravel的数据库导出
                return $this->createLaravelDatabaseBackup($filename);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '备份失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 创建文件系统备份
     */
    public function createFileBackup(Request $request)
    {
        try {
            $filename = 'files_backup_' . now()->format('Y-m-d_H-i-s') . '.zip';
            $backupPath = storage_path('app/backups');
            $filePath = $backupPath . '/' . $filename;
            
            // 确保备份目录存在
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }
            
            $zip = new ZipArchive();
            if ($zip->open($filePath, ZipArchive::CREATE) !== TRUE) {
                throw new \Exception('无法创建ZIP文件');
            }
            
            // 备份博客文件
            $this->addDirectoryToZip($zip, storage_path('blog'), 'blog');
            
            // 备份报告文件
            $this->addDirectoryToZip($zip, storage_path('reports'), 'reports');
            
            // 备份公共图片
            $this->addDirectoryToZip($zip, public_path('images'), 'public/images');
            
            // 备份配置文件
            $configFiles = [
                '.env' => base_path('.env'),
                'composer.json' => base_path('composer.json'),
                'package.json' => base_path('package.json'),
            ];
            
            foreach ($configFiles as $name => $path) {
                if (File::exists($path)) {
                    $zip->addFile($path, 'config/' . $name);
                }
            }
            
            $zip->close();
            
            return response()->json([
                'success' => true,
                'message' => '文件备份创建成功',
                'filename' => $filename,
                'size' => $this->formatFileSize(File::size($filePath))
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '文件备份失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 创建完整备份
     */
    public function createFullBackup(Request $request)
    {
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = 'full_backup_' . $timestamp . '.zip';
            $backupPath = storage_path('app/backups');
            $filePath = $backupPath . '/' . $filename;
            $tempDir = $backupPath . '/temp_' . $timestamp;
            
            // 确保目录存在
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }
            
            File::makeDirectory($tempDir, 0755, true);
            
            // 1. 创建数据库备份
            $dbBackupFile = $tempDir . '/database.sql';
            $this->exportDatabaseToFile($dbBackupFile);
            
            // 2. 创建ZIP文件包含所有内容
            $zip = new ZipArchive();
            if ($zip->open($filePath, ZipArchive::CREATE) !== TRUE) {
                throw new \Exception('无法创建ZIP文件');
            }
            
            // 添加数据库备份
            $zip->addFile($dbBackupFile, 'database.sql');
            
            // 添加文件系统内容
            $this->addDirectoryToZip($zip, storage_path('blog'), 'files/blog');
            $this->addDirectoryToZip($zip, storage_path('reports'), 'files/reports');
            $this->addDirectoryToZip($zip, public_path('images'), 'files/public/images');
            
            // 添加配置文件
            $configFiles = [
                '.env' => base_path('.env'),
                'composer.json' => base_path('composer.json'),
                'package.json' => base_path('package.json'),
                'routes/web.php' => base_path('routes/web.php'),
            ];
            
            foreach ($configFiles as $name => $path) {
                if (File::exists($path)) {
                    $zip->addFile($path, 'config/' . $name);
                }
            }
            
            // 添加备份信息文件
            $backupInfo = [
                'created_at' => now()->toISOString(),
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'database' => config('database.default'),
                'backup_type' => 'full',
                'description' => '完整系统备份，包含数据库、文件和配置'
            ];
            
            $zip->addFromString('backup_info.json', json_encode($backupInfo, JSON_PRETTY_PRINT));
            
            $zip->close();
            
            // 清理临时文件
            File::deleteDirectory($tempDir);
            
            return response()->json([
                'success' => true,
                'message' => '完整备份创建成功',
                'filename' => $filename,
                'size' => $this->formatFileSize(File::size($filePath))
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '完整备份失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 下载备份文件
     */
    public function download($filename)
    {
        $filePath = storage_path('app/backups/' . $filename);
        
        if (!File::exists($filePath)) {
            abort(404, '备份文件不存在');
        }
        
        return response()->download($filePath);
    }

    /**
     * 删除备份文件
     */
    public function delete($filename)
    {
        try {
            $filePath = storage_path('app/backups/' . $filename);
            
            if (File::exists($filePath)) {
                File::delete($filePath);
                return response()->json([
                    'success' => true,
                    'message' => '备份文件删除成功'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '备份文件不存在'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 清理旧备份
     */
    public function cleanup(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $backupPath = storage_path('app/backups');
            
            if (!File::exists($backupPath)) {
                return response()->json([
                    'success' => true,
                    'message' => '没有找到备份目录',
                    'deleted' => 0
                ]);
            }
            
            $files = File::files($backupPath);
            $deleted = 0;
            $cutoffTime = now()->subDays($days)->timestamp;
            
            foreach ($files as $file) {
                if (File::lastModified($file) < $cutoffTime) {
                    File::delete($file);
                    $deleted++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "已清理 {$deleted} 个超过 {$days} 天的备份文件",
                'deleted' => $deleted
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '清理失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 获取备份列表
     */
    private function getBackupList()
    {
        $backupPath = storage_path('app/backups');
        
        if (!File::exists($backupPath)) {
            return collect();
        }
        
        $files = File::files($backupPath);
        
        return collect($files)->map(function ($file) {
            $filename = basename($file);
            $size = File::size($file);
            $modified = File::lastModified($file);
            
            // 解析备份类型
            $type = 'unknown';
            if (strpos($filename, 'database_') === 0) {
                $type = 'database';
            } elseif (strpos($filename, 'files_') === 0) {
                $type = 'files';
            } elseif (strpos($filename, 'full_') === 0) {
                $type = 'full';
            }
            
            return [
                'filename' => $filename,
                'type' => $type,
                'size' => $size,
                'size_formatted' => $this->formatFileSize($size),
                'created_at' => Carbon::createFromTimestamp($modified),
                'age_days' => now()->diffInDays(Carbon::createFromTimestamp($modified)),
            ];
        })->sortByDesc('created_at');
    }

    /**
     * 获取备份统计信息
     */
    private function getBackupStats()
    {
        $backups = $this->getBackupList();
        
        return [
            'total_backups' => $backups->count(),
            'total_size' => $this->formatFileSize($backups->sum('size')),
            'database_backups' => $backups->where('type', 'database')->count(),
            'file_backups' => $backups->where('type', 'files')->count(),
            'full_backups' => $backups->where('type', 'full')->count(),
            'latest_backup' => $backups->first(),
            'oldest_backup' => $backups->last(),
        ];
    }

    /**
     * 使用Laravel导出数据库
     */
    private function createLaravelDatabaseBackup($filename)
    {
        $backupPath = storage_path('app/backups');
        $filePath = $backupPath . '/' . $filename;
        
        $sql = '';
        
        // 获取所有表
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $tableKey = 'Tables_in_' . $databaseName;
        
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            // 获取表结构
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $sql .= "\n\n-- Table structure for table `{$tableName}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable->{'Create Table'} . ";\n";
            
            // 获取表数据
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "\n-- Dumping data for table `{$tableName}`\n";
                foreach ($rows as $row) {
                    $values = array_map(function($value) {
                        return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }, (array) $row);
                    
                    $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                }
            }
        }
        
        File::put($filePath, $sql);
        
        return response()->json([
            'success' => true,
            'message' => '数据库备份创建成功（Laravel方式）',
            'filename' => $filename,
            'size' => $this->formatFileSize(File::size($filePath))
        ]);
    }

    /**
     * 导出数据库到文件
     */
    private function exportDatabaseToFile($filePath)
    {
        $sql = '';
        
        // 获取所有表
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $tableKey = 'Tables_in_' . $databaseName;
        
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            // 获取表结构
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $sql .= "\n\n-- Table structure for table `{$tableName}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable->{'Create Table'} . ";\n";
            
            // 获取表数据
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "\n-- Dumping data for table `{$tableName}`\n";
                foreach ($rows as $row) {
                    $values = array_map(function($value) {
                        return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }, (array) $row);
                    
                    $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                }
            }
        }
        
        File::put($filePath, $sql);
    }

    /**
     * 将目录添加到ZIP文件
     */
    private function addDirectoryToZip($zip, $dirPath, $zipPath = '')
    {
        if (!File::exists($dirPath)) {
            return;
        }
        
        $files = File::allFiles($dirPath);
        
        foreach ($files as $file) {
            $relativePath = str_replace($dirPath . '/', '', $file->getPathname());
            $zipFilePath = $zipPath ? $zipPath . '/' . $relativePath : $relativePath;
            $zip->addFile($file->getPathname(), $zipFilePath);
        }
    }

    /**
     * 格式化文件大小
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
