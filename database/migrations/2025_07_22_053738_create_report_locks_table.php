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
        Schema::create('report_locks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('报告的slug标识符');
            $table->string('label')->default('hackthebox')->comment('靶场标签，如hackthebox, tryhackme等');
            $table->string('title')->comment('报告标题');
            $table->text('password')->comment('访问密码，支持长hash如NTLM、shadow等');
            $table->text('description')->nullable()->comment('密码描述或提示');
            $table->boolean('is_enabled')->default(true)->comment('是否启用锁定');
            $table->timestamp('locked_at')->useCurrent()->comment('锁定时间');
            $table->timestamps();
            
            // 索引
            $table->index(['label', 'is_enabled']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_locks');
    }
};
