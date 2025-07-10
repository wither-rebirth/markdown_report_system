<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 检查是否已存在管理员账号
        $existingAdmin = User::where('email', 'admin@wither.com')->first();
        
        if (!$existingAdmin) {
            // 创建默认管理员账号
            User::create([
                'name' => 'Wither Admin',
                'email' => 'admin@wither.com',
                'password' => Hash::make('adminisprivate'), // 使用bcrypt加密
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('默认管理员账号创建成功！');
            $this->command->info('邮箱: admin@wither.com');
            $this->command->info('密码: adminisprivate');
        } else {
            // 如果已存在，更新密码和管理员权限
            $existingAdmin->update([
                'password' => Hash::make('adminisprivate'),
                'is_admin' => true,
            ]);
            
            $this->command->info('管理员账号已存在，已更新密码和权限！');
        }
    }
}
