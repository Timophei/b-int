<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Оформление заголовков: переносы строк из админки и фирменная голубая точка в конце.
 */
class Typo
{
    public static function title(?string $text): HtmlString
    {
        $text = trim((string) $text);
        $dot = '';
        if (str_ends_with($text, '.')) {
            $text = substr($text, 0, -1);
            $dot = '<span class="dot">.</span>';
        }

        return new HtmlString(nl2br(e($text), false).$dot);
    }
}
