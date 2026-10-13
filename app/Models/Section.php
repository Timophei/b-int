<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

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
        'bitrix' => 'Битрикс24',
        'about' => 'О нас',
        'courses' => 'Курсы',
        'contact' => 'Написать',
        'privacy' => 'Политика конфиденциальности',
    ];

    /**
     * Иконки для примеров и шагов (рисунки — в site/partials/icons).
     *
     * @var array<string, string>
     */
    public const ICONS = [
        'cart' => 'Корзина',
        'chat' => 'Сообщение',
        'leaf' => 'Лист',
        'doc' => 'Документ',
        'book' => 'Книга',
        'gear' => 'Шестерёнка',
        'plug' => 'Подключение',
        'target' => 'Цель',
        'people' => 'Люди',
    ];

    /**
     * Фоновые картинки шапки по разделам, пока в админке не загружена своя.
     *
     * @var array<string, string>
     */
    private const HERO_IMAGES = [
        'ai' => 'hero-ai',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'in_nav' => 'boolean',
            'process' => 'array',
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

    public function cases(): HasMany
    {
        return $this->hasMany(CaseStudy::class)->orderBy('sort')->orderBy('id');
    }

    public function plan(): HasOne
    {
        return $this->hasOne(Plan::class)->where('group', 'main')->orderBy('sort')->orderBy('id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class)->orderBy('sort')->orderBy('id');
    }

    /** Разделы для меню и главной: опубликованные, по порядку, без главной и служебных. */
    public static function navigation(): Collection
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

    /**
     * Картинка шапки: своя из админки или фирменная по разделу.
     *
     * @return array{src: string, srcset: ?string}
     */
    public function heroImage(): array
    {
        if ($this->cover) {
            return ['src' => '/storage/'.$this->cover, 'srcset' => null];
        }

        $name = self::HERO_IMAGES[$this->key] ?? 'hero';

        return ['src' => "/img/{$name}.jpg", 'srcset' => "/img/{$name}-1024.jpg 1024w, /img/{$name}.jpg 2048w"];
    }

    /** Номер раздела в оформлении: 01, 02… */
    public function number(): string
    {
        $index = static::navigation()->search(fn (self $s) => $s->is($this));

        return str_pad((string) (($index === false ? 0 : $index) + 1), 2, '0', STR_PAD_LEFT);
    }
}
