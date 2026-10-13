<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Тариф раздела: цена, что входит, кнопка и сноска.
 */
class Plan extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
