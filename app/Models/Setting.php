<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Общие настройки сайта — одна строка в таблице.
 */
class Setting extends Model
{
    protected $guarded = ['id'];

    private static ?self $current = null;

    public static function current(): self
    {
        if (static::$current?->exists) {
            return static::$current;
        }

        // До первой миграции (установка, package:discover) таблицы ещё нет
        if (! Schema::hasTable('settings')) {
            return new static(['company_name' => 'Байкал Интеграция']);
        }

        return static::$current = static::query()->first() ?? static::query()->create([]);
    }

    public static function forget(): void
    {
        static::$current = null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::forget());
    }

    public function telegramUrl(): ?string
    {
        $value = trim((string) $this->telegram);
        if ($value === '') {
            return null;
        }

        return str_starts_with($value, 'http') ? $value : 'https://t.me/'.ltrim($value, '@');
    }

    public function whatsappUrl(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->whatsapp);

        return $digits ? 'https://wa.me/'.$digits : null;
    }

    public function phoneHref(): ?string
    {
        $digits = preg_replace('/[^\d+]+/', '', (string) $this->phone);

        return $digits ? 'tel:'.$digits : null;
    }

    public function metrikaId(): ?int
    {
        $id = $this->metrika_id ?: config('services.metrika.id');

        return $id ? (int) $id : null;
    }
}
