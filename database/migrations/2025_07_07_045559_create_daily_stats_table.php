<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique(); // 统计日期
            $table->unsignedInteger('total_pv')->default(0); // 总页面浏览量
            $table->unsignedInteger('total_uv')->default(0); // 总独立访客
            $table->unsignedInteger('new_visitors')->default(0); // 新访客数
            $table->unsignedInteger('returning_visitors')->default(0); // 回访客数
            $table->unsignedInteger('bounce_rate')->default(0); // 跳出率(百分比)
            $table->unsignedInteger('avg_session_duration')->default(0); // 平均会话时长(秒)
            $table->json('top_pages')->nullable(); // 热门页面 JSON
            $table->json('top_referers')->nullable(); // 热门来源 JSON
            $table->json('device_stats')->nullable(); // 设备统计 JSON
            $table->json('browser_stats')->nullable(); // 浏览器统计 JSON
            $table->json('os_stats')->nullable(); // 操作系统统计 JSON
            $table->json('country_stats')->nullable(); // 国家统计 JSON
            $table->timestamps();
            
            // 添加索引
            $table->index('date');
            $table->index(['date', 'total_pv']);
            $table->index(['date', 'total_uv']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_stats');
    }
};
