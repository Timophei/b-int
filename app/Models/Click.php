<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Нажатие на контакт или кнопку на сайте.
 */
class Click extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var array<string, string>
     */
    public const KINDS = [
        'email' => 'Почта',
        'phone' => 'Телефон',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp',
        'max' => 'MAX',
        'cta' => 'Кнопка',
        'external' => 'Переход на другой сайт',
    ];

    protected $guarded = ['id'];
}
