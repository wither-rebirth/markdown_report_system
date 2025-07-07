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
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('url', 500)->index(); // 访问的URL
            $table->ipAddress('ip_address')->index(); // 访问者IP
            $table->text('user_agent')->nullable(); // 用户代理
            $table->string('referer', 500)->nullable(); // 来源页面
            $table->string('session_id', 100)->index(); // 会话ID
            $table->unsignedBigInteger('user_id')->nullable()->index(); // 用户ID（如果已登录）
            $table->string('country', 100)->nullable(); // 国家
            $table->string('city', 100)->nullable(); // 城市
            $table->string('device_type', 50)->nullable(); // 设备类型
            $table->string('browser', 100)->nullable(); // 浏览器
            $table->string('os', 100)->nullable(); // 操作系统
            $table->timestamp('visited_at')->index(); // 访问时间
            $table->timestamps();
            
            // 添加索引
            $table->index(['url', 'visited_at']);
            $table->index(['ip_address', 'visited_at']);
            $table->index(['session_id', 'visited_at']);
            
            // 外键约束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
