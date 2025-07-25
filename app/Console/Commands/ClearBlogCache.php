<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ClearBlogCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:clear-cache {--force : 强制清除所有缓存}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清除blog相关的缓存';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始清除blog缓存...');
        
        $blogDir = storage_path('blog');
        $clearedCount = 0;
        
        try {
            // 清除文件状态缓存
            clearstatcache();
            
            // 尝试清除可能的blog_posts_缓存键
            $currentTime = filemtime($blogDir);
            $possibleKeys = [
                'blog_posts_' . ($currentTime - 2),
                'blog_posts_' . ($currentTime - 1),
                'blog_posts_' . $currentTime,
                'blog_posts_' . ($currentTime + 1),
            ];
            
            foreach ($possibleKeys as $key) {
                if (Cache::forget($key)) {
                    $clearedCount++;
                    $this->line("✓ 清除缓存: {$key}");
                }
            }
            
            // 触碰blog目录以更新修改时间
            if (File::exists($blogDir)) {
                touch($blogDir);
                $this->line("✓ 更新blog目录修改时间");
            }
            
            // 如果使用--force参数，清除所有缓存
            if ($this->option('force')) {
                Cache::flush();
                $this->warn("⚠️  已清除所有缓存（使用了--force参数）");
            }
            
            if ($clearedCount > 0) {
                $this->info("✅ 成功清除 {$clearedCount} 个blog缓存");
            } else {
                $this->comment("ℹ️  没有找到需要清除的blog缓存");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ 清除缓存时出错: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
