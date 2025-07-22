<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\MakeReportCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // 注册自定义命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeReportCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // 设置默认分页视图
        \Illuminate\Pagination\Paginator::defaultView('pagination.default');
        \Illuminate\Pagination\Paginator::defaultSimpleView('pagination.simple-default');
    }
} 