<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 設置 Carbon 預設時區為 Asia/Taipei
        \Carbon\Carbon::setLocale('zh_TW');
        date_default_timezone_set('Asia/Taipei');
    }
}
