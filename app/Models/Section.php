<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Раздел сайта — отдельная страница со своим адресом (или ссылка на внешний сайт).
 */
class Section extends Model
{
    /**
     * Разделы, которые сайт знает по ключу. Остальные ключи — обычные текстовые страницы.
     *
     * @var array<string, string>
     */
    public const KEYS = [
        'home' => 'Главная',
        'support' => 'Поддержка',
        'ai' => 'ИИ сотрудники',
        'courses' => 'Курсы',
        'contact' => 'Написать',
        'privacy' => 'Политика конфиденциальности',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'in_nav' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('sort')->orderBy('id');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class)->orderBy('sort')->orderBy('id');
    }

    public function plan(): HasOne
    {
        return $this->hasOne(Plan::class)->orderBy('sort')->orderBy('id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class)->orderBy('sort')->orderBy('id');
    }

    /** Разделы для меню и главной: опубликованные, по порядку, без главной и служебных. */
    public static function navigation(): \Illuminate\Support\Collection
    {
        return static::query()->where('in_nav', true)
            ->when(! auth()->check(), fn (Builder $q) => $q->where('is_published', true))
            ->whereNotIn('key', ['home', 'contact', 'privacy'])
            ->orderBy('sort')->orderBy('id')->get();
    }

    public static function byKey(string $key): ?self
    {
        return static::firstWhere('key', $key);
    }

    public function isExternal(): bool
    {
        return filled($this->external_url);
    }

    public function url(): string
    {
        if ($this->isExternal()) {
            return $this->external_url;
        }

        return $this->path === '' || $this->path === null ? '/' : '/'.trim($this->path, '/').'/';
    }

    /** Номер раздела в оформлении: 01, 02… */
    public function number(): string
    {
        $index = static::navigation()->search(fn (self $s) => $s->is($this));

        return str_pad((string) (($index === false ? 0 : $index) + 1), 2, '0', STR_PAD_LEFT);
    }
}
