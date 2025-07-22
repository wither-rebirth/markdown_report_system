<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 每小时同步一次报告锁定（检测新的报告文件）
        $schedule->command('reports:sync-locks')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/sync-locks.log'));
        
        // 每天清理过期的缓存
        $schedule->command('cache:clear')
            ->daily()
            ->at('03:00')
            ->appendOutputTo(storage_path('logs/cache-clear.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 