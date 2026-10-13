<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Настройки сайта (контакты, ссылка на курсы) нужны в шапке и подвале каждой страницы
        View::composer('site.*', function (\Illuminate\View\View $view): void {
            $view->with('settings', Setting::current());
        });
    }
}
