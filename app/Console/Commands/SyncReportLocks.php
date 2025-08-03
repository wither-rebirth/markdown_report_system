<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportLock;
use Illuminate\Support\Facades\File;

class SyncReportLocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:sync-locks {--auto-enable : Auto enable locks for new reports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync report locks with actual report files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Syncing report locks with actual files...');
        $this->newLine();
        
        $autoEnable = $this->option('auto-enable');
        $created = 0;
        $updated = 0;
        $disabled = 0;
        
        // 获取所有实际存在的报告
        $actualReports = $this->getAllActualReports();
        
        // 为新的报告创建锁定记录
        foreach ($actualReports as $report) {
            $existingLock = ReportLock::where('slug', $report['slug'])->first();
            
            if (!$existingLock) {
                // 创建新的锁定记录
                ReportLock::create([
                    'slug' => $report['slug'],
                    'label' => $report['label'],
                    'title' => $report['title'],
                    'password' => $this->generateDefaultPassword($report),
                    'description' => $this->generateDefaultDescription($report),
                    'is_enabled' => $autoEnable
                ]);
                
                $this->line("✅ Created lock for: {$report['slug']}");
                $created++;
            } else {
                // 更新现有记录的标题（如果需要）
                if ($existingLock->title !== $report['title']) {
                    $existingLock->update(['title' => $report['title']]);
                    $this->line("🔄 Updated title for: {$report['slug']}");
                    $updated++;
                }
            }
        }
        
        // 禁用不存在的报告的锁定
        $actualSlugs = collect($actualReports)->pluck('slug');
        $obsoleteLocks = ReportLock::whereNotIn('slug', $actualSlugs)->where('is_enabled', true)->get();
        
        foreach ($obsoleteLocks as $lock) {
            // 检查是否是测试数据
            if (!str_starts_with($lock->slug, 'test-')) {
                $lock->update(['is_enabled' => false]);
                $this->line("⚠️  Disabled lock for missing file: {$lock->slug}");
                $disabled++;
            }
        }
        
        $this->newLine();
        $this->info("📊 Sync completed!");
        $this->table(
            ['Action', 'Count'],
            [
                ['Created', $created],
                ['Updated', $updated],
                ['Disabled', $disabled],
                ['Total Reports Found', count($actualReports)],
            ]
        );
        
        if ($created > 0 && !$autoEnable) {
            $this->warn('💡 Tip: New locks are created as disabled. Use --auto-enable to enable them automatically.');
        }
        
        return 0;
    }
    
    /**
     * 获取所有实际存在的报告
     */
    private function getAllActualReports(): array
    {
        $reports = [];
        $reportsDir = storage_path('reports');
        $hacktheboxDir = storage_path('reports/Hackthebox-Walkthrough');
        
        // 获取普通报告文件
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
                    'label' => 'other',
                    'type' => 'normal',
                    'path' => $file
                ];
            }
        }
        
        // 获取HackTheBox报告 (新的按难度分级的结构)
        if (File::exists($hacktheboxDir) && File::isDirectory($hacktheboxDir)) {
            $difficultyDirs = ['Easy', 'Medium', 'Hard', 'Insane', 'Fortresses'];
            
            foreach ($difficultyDirs as $difficulty) {
                $difficultyPath = $hacktheboxDir . '/' . $difficulty;
                
                if (File::exists($difficultyPath) && File::isDirectory($difficultyPath)) {
                    $machineDirectories = File::directories($difficultyPath);
                    
                    foreach ($machineDirectories as $machineDir) {
                        $machineName = basename($machineDir);
                        $walkthroughFile = $machineDir . '/Walkthrough.md';
                        
                        if (File::exists($walkthroughFile)) {
                            // 对于Fortresses使用不同的标题格式
                            $title = $difficulty === 'Fortresses' 
                                ? $machineName . ' - HackTheBox Fortress'
                                : $machineName . ' - HackTheBox Writeup (' . $difficulty . ')';
                                
                            $reports[] = [
                                'slug' => 'htb-' . strtolower($difficulty) . '-' . $machineName,
                                'title' => $title,
                                'label' => 'hackthebox',
                                'type' => 'hackthebox',
                                'path' => $walkthroughFile,
                                'machine_name' => $machineName,
                                'difficulty' => $difficulty
                            ];
                        }
                    }
                }
            }
        }
        
        return $reports;
    }
    
    /**
     * 为新报告生成默认密码
     */
    private function generateDefaultPassword($report): string
    {
        if ($report['type'] === 'hackthebox') {
            // 为HTB机器生成提示性的默认密码，包含难度信息
            $difficulty = isset($report['difficulty']) ? strtolower($report['difficulty']) : 'unknown';
            return 'change_me_' . strtolower($report['machine_name']) . '_' . $difficulty . '_hash';
        }
        
        return 'change_me_default_password';
    }
    
    /**
     * 为新报告生成默认描述
     */
    private function generateDefaultDescription($report): string
    {
        if ($report['type'] === 'hackthebox') {
            $difficulty = isset($report['difficulty']) ? $report['difficulty'] : 'Unknown';
            if ($difficulty === 'Fortresses') {
                return "请设置 {$report['machine_name']} Fortress 的相应密码";
            }
            return "请设置 {$report['machine_name']} 机器（{$difficulty}）的相应密码（如用户hash、root密码等）";
        }
        
        return '请设置此报告的访问密码';
    }
}
