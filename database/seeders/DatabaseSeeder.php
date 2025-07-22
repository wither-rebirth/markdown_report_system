<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            // ReportLockSeeder::class, // 按需手动运行：php artisan db:seed --class=ReportLockSeeder
        ]);
    }
} 