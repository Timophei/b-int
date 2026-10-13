<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Заявка с формы «Написать».
 */
class Lead extends Model
{
    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'new' => 'Новая',
        'work' => 'В работе',
        'done' => 'Закрыта',
        'spam' => 'Спам',
    ];

    protected $guarded = ['id'];
}
