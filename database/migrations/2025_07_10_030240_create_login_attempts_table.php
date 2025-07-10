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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45); // 支持IPv6
            $table->string('email')->nullable(); // 登录时使用的邮箱
            $table->boolean('successful')->default(false); // 是否登录成功
            $table->text('user_agent')->nullable(); // 用户代理信息
            $table->timestamps();
            
            // 索引优化
            $table->index(['ip_address', 'created_at']);
            $table->index(['email', 'created_at']);
            $table->index(['ip_address', 'successful', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
