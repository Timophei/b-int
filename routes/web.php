<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::post('/a/hit', [AnalyticsController::class, 'hit'])->middleware('throttle:120,1')->name('analytics.hit');
Route::post('/a/click', [SiteController::class, 'click'])->middleware('throttle:60,1')->name('analytics.click');
Route::post('/zayavka', [SiteController::class, 'lead'])->middleware('throttle:6,1')->name('lead');

// Разделы живут по адресам из админки. Маршрут последний, чтобы не перехватывать остальные.
Route::get('/{path}', [SiteController::class, 'page'])
    ->where('path', '^(?!admin|livewire|storage|up$)[A-Za-z0-9_\-\/]+$')
    ->name('section');
