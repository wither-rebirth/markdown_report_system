<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password {email} {password}';
    protected $description = 'Reset admin user password';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // 查找用户
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }

        if (!$user->is_admin) {
            $this->error("User {$email} is not an admin!");
            return 1;
        }

        // 更新密码
        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password for admin {$email} has been reset successfully!");
        return 0;
    }
} 