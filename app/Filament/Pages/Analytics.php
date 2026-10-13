<?php

namespace App\Filament\Pages;

use App\Models\Click;
use App\Models\Lead;
use App\Models\PageView;
use App\Models\Section;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Встроенная аналитика: посетители, источники, страницы, метки, нажатия и заявки.
 */
class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Аналитика';

    protected static ?string $title = 'Аналитика';

    protected static ?string $slug = 'analitika';

    protected static ?int $navigationSort = -1;

    protected static string $view = 'filament.pages.analytics';

    /**
     * @var array<int, string>
     */
    public const PERIODS = [1 => 'Сегодня', 7 => '7 дней', 30 => '30 дней', 90 => '90 дней', 365 => 'Год'];

    public int $days = 30;

    public function setDays(int $days): void
    {
        $this->days = array_key_exists($days, self::PERIODS) ? $days : 30;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $today = CarbonImmutable::now(config('app.site_timezone'))->startOfDay();
        $from = $today->subDays($this->days - 1);
        $views = fn (): Builder => PageView::query()->where('day', '>=', $from->toDateString());
        $entries = fn (): Builder => $views()->where('is_entry', true);
        $uniqueVisitors = "count(distinct day || '-' || visitor)";

        $daily = $views()->selectRaw("day, count(*) as views, {$uniqueVisitors} as visitors")
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');
        $series = collect(range(0, $this->days - 1))->map(function (int $offset) use ($from, $daily): array {
            $day = $from->addDays($offset)->toDateString();

            return ['day' => $day, 'views' => (int) ($daily[$day]->views ?? 0), 'visitors' => (int) ($daily[$day]->visitors ?? 0)];
        });

        $clicks = Click::query()->where('created_at', '>=', $from->setTimezone('UTC'));
        $leads = Lead::query()->where('created_at', '>=', $from->setTimezone('UTC'))->where('status', '!=', 'spam');

        return [
            'periods' => self::PERIODS,
            'totals' => [
                'Посетители' => (int) $views()->selectRaw("{$uniqueVisitors} as c")->value('c'),
                'Просмотры' => $views()->count(),
                'Заходы на сайт' => $entries()->count(),
                'Нажатия' => (clone $clicks)->count(),
                'Заявки' => (clone $leads)->count(),
            ],
            'series' => $series,
            'sources' => $this->rows($entries()->selectRaw("source, count(*) as entries, {$uniqueVisitors} as visitors")->groupBy('source'), 'entries')
                ->map(fn ($row) => [PageView::SOURCES[$row->source] ?? $row->source, $row->entries, $row->visitors]),
            'sourceNames' => $this->rows($entries()->whereNotIn('source', ['direct', 'internal'])
                ->selectRaw("source, source_name, count(*) as entries, {$uniqueVisitors} as visitors")->groupBy('source', 'source_name'), 'entries')
                ->map(fn ($row) => [$row->source_name, PageView::SOURCES[$row->source] ?? $row->source, $row->entries, $row->visitors]),
            'campaigns' => $this->rows($entries()->whereNotNull('utm_source')
                ->selectRaw("utm_source, utm_medium, utm_campaign, count(*) as entries, {$uniqueVisitors} as visitors")
                ->groupBy('utm_source', 'utm_medium', 'utm_campaign'), 'entries')
                ->map(fn ($row) => [$row->utm_source, $row->utm_medium ?: '—', $row->utm_campaign ?: '—', $row->entries, $row->visitors]),
            'pages' => $this->withTitles($this->rows($views()->selectRaw("path, count(*) as views, {$uniqueVisitors} as visitors")->groupBy('path'), 'views', 30))
                ->map(fn ($row) => [$row->title, $row->path, $row->views, $row->visitors]),
            'landings' => $this->withTitles($this->rows($entries()->selectRaw("path, count(*) as entries, {$uniqueVisitors} as visitors")->groupBy('path'), 'entries', 15))
                ->map(fn ($row) => [$row->title, $row->path, $row->entries, $row->visitors]),
            'devices' => $this->rows($views()->selectRaw("device, {$uniqueVisitors} as visitors, count(*) as views")->groupBy('device'), 'visitors')
                ->map(fn ($row) => [PageView::DEVICES[$row->device] ?? $row->device, $row->visitors, $row->views]),
            'contacts' => $clicks->select('kind', 'label', DB::raw('count(*) as clicks'))->groupBy('kind', 'label')
                ->orderByDesc('clicks')->limit(30)->get()
                ->map(fn (Click $row) => [Click::KINDS[$row->kind] ?? $row->kind, $row->label ?: '—', $row->clicks]),
            'leadSources' => $leads->toBase()->selectRaw('source, source_name, count(*) as leads')->groupBy('source', 'source_name')
                ->orderByDesc('leads')->limit(20)->get()
                ->map(fn ($row) => [$row->source_name ?: (PageView::SOURCES[$row->source] ?? ($row->source ?: '—')), PageView::SOURCES[$row->source] ?? '—', $row->leads]),
        ];
    }

    /**
     * Самые частые строки отчёта.
     *
     * @return Collection<int, object>
     */
    private function rows(Builder $query, string $orderBy, int $limit = 20): Collection
    {
        return $query->toBase()->orderByDesc($orderBy)->limit($limit)->get();
    }

    /**
     * Подставляет к адресам страниц названия разделов.
     *
     * @param  Collection<int, object>  $rows
     * @return Collection<int, object>
     */
    private function withTitles(Collection $rows): Collection
    {
        $titles = Section::all()->mapWithKeys(fn (Section $s) => [$s->url() => $s->nav_label]);

        return $rows->map(function (object $row) use ($titles): object {
            $row->title = $row->path === '/' ? 'Главная' : ($titles[$row->path] ?? $row->path);

            return $row;
        });
    }
}
