<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportLock;

class EnableReportLock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:enable-lock {slug} {password} {--description=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable lock for a specific report with password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $slug = $this->argument('slug');
        $password = $this->argument('password');
        $description = $this->option('description') ?: '密码已设置';
        
        $lock = ReportLock::where('slug', $slug)->first();
        
        if (!$lock) {
            $this->error("❌ Report lock not found for slug: {$slug}");
            $this->info("💡 Tip: Run 'php artisan reports:sync-locks' first to create lock records.");
            return 1;
        }
        
        $lock->update([
            'password' => $password,
            'description' => $description,
            'is_enabled' => true
        ]);
        
        $this->info("✅ Lock enabled for: {$slug}");
        $this->info("🔒 Password: " . substr($password, 0, 20) . (strlen($password) > 20 ? '...' : ''));
        $this->info("📝 Description: {$description}");
        
        return 0;
    }
}
