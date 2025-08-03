<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportLock;

class CleanReportLocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:clean 
                            {--show : Show current database status}
                            {--clean : Remove all report locks}
                            {--clean-old : Remove old format locks only}
                            {--force : Force operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up messy report locks database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('show')) {
            $this->showStatus();
            return 0;
        }

        if ($this->option('clean')) {
            $this->cleanAll();
            return 0;
        }

        if ($this->option('clean-old')) {
            $this->cleanOldFormat();
            return 0;
        }

        // 默认显示状态和选项
        $this->showStatus();
        $this->newLine();
        $this->info('Available options:');
        $this->line('  --show        Show current database status');
        $this->line('  --clean       Remove ALL report locks (use with --force)');
        $this->line('  --clean-old   Remove old format locks only');
        $this->line('  --force       Skip confirmations');

        return 0;
    }

    private function showStatus()
    {
        $this->info('📊 Current Report Locks Database Status');
        $this->newLine();

        $total = ReportLock::count();
        $enabled = ReportLock::where('is_enabled', true)->count();
        $disabled = ReportLock::where('is_enabled', false)->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Locks', $total],
                ['Enabled', $enabled],
                ['Disabled', $disabled],
            ]
        );

        if ($total > 0) {
            $this->newLine();
            $this->info('🔍 Slug Format Analysis:');

            // 分析不同的 slug 格式
            $locks = ReportLock::select('slug')->get();
            
            $newFormat = $locks->filter(function($lock) {
                return preg_match('/^htb-(easy|medium|hard|insane|fortresses)-/', $lock->slug);
            });

            $oldFormat = $locks->filter(function($lock) {
                return preg_match('/^htb-[A-Za-z]+$/', $lock->slug); // htb-MachineName 格式
            });

            $testFormat = $locks->filter(function($lock) {
                return preg_match('/^test-/', $lock->slug);
            });

            $otherHtbFormat = $locks->filter(function($lock) {
                return preg_match('/^htb-/', $lock->slug) && 
                       !preg_match('/^htb-(easy|medium|hard|insane|fortresses)-/', $lock->slug) &&
                       !preg_match('/^htb-[A-Za-z]+$/', $lock->slug);
            });

            $otherFormat = $locks->filter(function($lock) {
                return !preg_match('/^htb-/', $lock->slug) && !preg_match('/^test-/', $lock->slug);
            });

            $this->table(
                ['Format Type', 'Count', 'Examples'],
                [
                    ['New Format (htb-difficulty-Machine)', $newFormat->count(), $newFormat->take(3)->pluck('slug')->join(', ')],
                    ['Old Format (htb-Machine)', $oldFormat->count(), $oldFormat->take(3)->pluck('slug')->join(', ')],
                    ['Other HTB Format', $otherHtbFormat->count(), $otherHtbFormat->take(3)->pluck('slug')->join(', ')],
                    ['Test Records', $testFormat->count(), $testFormat->take(3)->pluck('slug')->join(', ')],
                    ['Other/Normal', $otherFormat->count(), $otherFormat->take(3)->pluck('slug')->join(', ')],
                ]
            );

            $totalClassified = $newFormat->count() + $oldFormat->count() + $otherHtbFormat->count() + $testFormat->count() + $otherFormat->count();
            if ($totalClassified !== $locks->count()) {
                $this->newLine();
                $this->error("⚠️  Classification mismatch! Total: {$locks->count()}, Classified: {$totalClassified}");
            }

            if ($oldFormat->count() > 0 && $newFormat->count() > 0) {
                $this->newLine();
                $this->warn('⚠️  Detected mixed format locks! This can cause conflicts.');
                $this->line('💡 Consider using --clean-old to remove old format locks.');
            }
        }
    }

    private function cleanAll()
    {
        $total = ReportLock::count();
        
        if ($total === 0) {
            $this->info('✅ No locks to clean.');
            return;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("⚠️  This will delete ALL {$total} report locks. Continue?")) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $deleted = ReportLock::count();
        ReportLock::truncate();
        
        $this->info("✅ Deleted {$deleted} report locks.");
        $this->line('💡 You can now run "php artisan reports:sync-locks" for a clean sync.');
    }

    private function cleanOldFormat()
    {
        // 查找旧格式的锁 (htb-MachineName 格式，不包含难度)
        $oldLocks = ReportLock::where('slug', 'like', 'htb-%')
            ->where('slug', 'not like', 'htb-easy-%')
            ->where('slug', 'not like', 'htb-medium-%')
            ->where('slug', 'not like', 'htb-hard-%')
            ->where('slug', 'not like', 'htb-insane-%')
            ->where('slug', 'not like', 'htb-fortresses-%')
            ->get();

        if ($oldLocks->count() === 0) {
            $this->info('✅ No old format locks found.');
            return;
        }

        $this->info("🔍 Found {$oldLocks->count()} old format locks:");
        $oldLocks->take(10)->each(function($lock) {
            $this->line("  - {$lock->slug}");
        });

        if ($oldLocks->count() > 10) {
            $this->line("  ... and " . ($oldLocks->count() - 10) . " more");
        }

        if (!$this->option('force')) {
            if (!$this->confirm("Delete these {$oldLocks->count()} old format locks?")) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $deleted = $oldLocks->count();
        ReportLock::whereIn('id', $oldLocks->pluck('id'))->delete();
        
        $this->info("✅ Deleted {$deleted} old format locks.");
        $this->line('💡 You can now run "php artisan reports:sync-locks" to sync any missing reports.');
    }
}