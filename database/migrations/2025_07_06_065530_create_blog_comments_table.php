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
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->string('blog_slug', 255)->index(); // 关联的博客文章slug
            $table->string('author_name', 100); // 评论者名字
            $table->text('content'); // 评论内容
            $table->ipAddress('ip_address')->nullable(); // IP地址
            $table->string('user_agent', 500)->nullable(); // 用户代理
            $table->boolean('is_approved')->default(true); // 是否审核通过
            $table->timestamps();
            
            // 索引
            $table->index(['blog_slug', 'is_approved', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
    }
};
