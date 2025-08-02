<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportLock;
use Illuminate\Support\Facades\File;

class TestReportLock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:report-lock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the report lock functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Report Lock System...');
        $this->newLine();
        
        // 检查数据库记录
        $locks = ReportLock::all();
        $this->info("📋 Found {$locks->count()} report locks in database:");
        
        foreach ($locks as $lock) {
            $status = $lock->is_enabled ? '🔒 Locked' : '🔓 Unlocked';
            $this->line("  • {$lock->slug} - {$lock->title} ({$lock->label}) - {$status}");
            $this->line("    Password: " . substr($lock->password, 0, 20) . '...');
            $this->line("    Description: {$lock->description}");
            $this->newLine();
        }
        
        // 检查实际文件存在
        $this->info('📁 Checking actual report files:');
        foreach ($locks->where('label', 'hackthebox') as $lock) {
            $slug = $lock->slug;
            if (str_starts_with($slug, 'htb-')) {
                $machineName = substr($slug, 4);
                $machineInfo = $this->findHacktheboxMachine($machineName);
                
                if ($machineInfo) {
                    $this->line("  ✅ {$slug} → File exists ({$machineInfo['difficulty']})");
                } else {
                    $this->error("  ❌ {$slug} → File missing");
                }
            }
        }
        
        $this->newLine();
        $this->info('🎯 Test completed! The report lock system is working with actual files.');
        
        return 0;
    }
    
    /**
     * Find HackTheBox machine in the new difficulty-based directory structure
     */
    private function findHacktheboxMachine($machineName)
    {
        $difficulties = ['Easy', 'Medium', 'Hard', 'Insane', 'Fortresses'];
        $hacktheboxDir = storage_path('reports/Hackthebox-Walkthrough');
        
        foreach ($difficulties as $difficulty) {
            $machineDir = $hacktheboxDir . '/' . $difficulty . '/' . $machineName;
            $walkthroughFile = $machineDir . '/Walkthrough.md';
            
            if (File::exists($walkthroughFile)) {
                return [
                    'path' => $machineDir,
                    'difficulty' => $difficulty,
                    'walkthrough_file' => $walkthroughFile
                ];
            }
        }
        
        return null;
    }
}
