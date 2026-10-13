<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Analytics;
use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use App\Models\PageView;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Сводка на первой странице админки: новые заявки и посетители за неделю.
 */
class SiteStats extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $today = CarbonImmutable::now(config('app.site_timezone'))->startOfDay();
        $week = $today->subDays(6)->toDateString();
        $daily = PageView::where('day', '>=', $week)
            ->selectRaw("day, count(distinct visitor) as visitors")->groupBy('day')->pluck('visitors', 'day');
        $chart = collect(range(6, 0))->map(fn (int $d) => (int) ($daily[$today->subDays($d)->toDateString()] ?? 0))->all();

        return [
            Stat::make('Новые заявки', Lead::where('status', 'new')->count())
                ->description('Всего заявок: '.Lead::where('status', '!=', 'spam')->count())
                ->url(LeadResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'new']]]))
                ->color('danger'),
            Stat::make('Посетители сегодня', (int) ($daily[$today->toDateString()] ?? 0))
                ->chart($chart)->color('primary')->url(Analytics::getUrl()),
            Stat::make('Посетители за 7 дней', array_sum($chart))
                ->description('Подробно — в разделе «Аналитика»')->url(Analytics::getUrl()),
        ];
    }
}
