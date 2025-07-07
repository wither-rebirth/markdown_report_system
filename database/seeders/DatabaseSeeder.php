<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * 运行数据库填充
     */
    public function run(): void
    {
        // 创建管理员用户
        $admin = User::firstOrCreate([
            'email' => 'admin@admin.com'
        ], [
            'name' => '管理员',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        echo "✅ 管理员用户已创建: {$admin->email} / password\n";

        // 创建默认分类
        $categories = [
            ['name' => '技术分享', 'slug' => 'tech', 'description' => '技术相关的文章和教程', 'sort_order' => 1],
            ['name' => '生活随笔', 'slug' => 'life', 'description' => '日常生活的感悟和记录', 'sort_order' => 2],
            ['name' => '项目展示', 'slug' => 'projects', 'description' => '个人项目和作品展示', 'sort_order' => 3],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::firstOrCreate([
                'slug' => $categoryData['slug']
            ], $categoryData);
            echo "✅ 分类已创建: {$category->name}\n";
        }

        // 创建默认标签
        $tags = [
            ['name' => 'Laravel', 'slug' => 'laravel', 'color' => '#ff2d20'],
            ['name' => 'PHP', 'slug' => 'php', 'color' => '#777bb4'],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'color' => '#f7df1e'],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'color' => '#4fc08d'],
            ['name' => '前端开发', 'slug' => 'frontend', 'color' => '#61dafb'],
            ['name' => '后端开发', 'slug' => 'backend', 'color' => '#68217a'],
            ['name' => '教程', 'slug' => 'tutorial', 'color' => '#28a745'],
            ['name' => '随笔', 'slug' => 'essay', 'color' => '#6c757d'],
        ];

        foreach ($tags as $tagData) {
            $tag = Tag::firstOrCreate([
                'slug' => $tagData['slug']
            ], $tagData);
            echo "✅ 标签已创建: {$tag->name}\n";
        }

        echo "\n🎉 数据库初始化完成！\n";
        echo "管理端登录信息：\n";
        echo "URL: /admin/login\n";
        echo "邮箱: admin@admin.com\n";
        echo "密码: password\n";
    }
} 