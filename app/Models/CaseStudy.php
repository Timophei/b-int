<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Пример работы в разделе: «Консультант интернет-магазина» у «Города чая».
 */
class CaseStudy extends Model
{
    protected $table = 'cases';

    protected $guarded = ['id'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
