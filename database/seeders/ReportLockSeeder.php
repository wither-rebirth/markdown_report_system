<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReportLock;

class ReportLockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清除旧数据
        ReportLock::truncate();
        
        // 创建一个简单的示例（可选，用于演示）
        ReportLock::create([
            'slug' => 'example-htb-machine',
            'label' => 'hackthebox',
            'title' => 'Example HTB Machine Writeup',
            'password' => 'example_password_123',
            'description' => '示例密码，用于演示密码保护功能',
            'is_enabled' => false // 默认禁用，避免影响实际使用
        ]);
        
        $this->command->info('Report locks seeded successfully!');
    }
}
