<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Тариф раздела: цена, что входит, кнопка и сноска.
 */
class Plan extends Model
{
    /**
     * @var array<string, string>
     */
    public const GROUPS = [
        'main' => 'Карточка рядом с услугами',
        'setup' => 'Разовая настройка',
        'monthly' => 'Ежемесячный тариф',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
        ];
    }

    /** Ссылка на PDF-оффер, если файл загружен в админке. */
    public function offerPdfUrl(): ?string
    {
        return $this->offer_pdf ? '/storage/'.$this->offer_pdf : null;
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
