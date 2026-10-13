<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Оформление заголовков: переносы строк из админки и фирменная голубая точка.
 * Точка или вопрос в самом конце подсвечиваются сами; «^» перед знаком подсвечивает его
 * в середине текста: «Ваши знания^.» → голубая точка после «знания».
 */
class Typo
{
    public static function title(?string $text, bool $accentEnd = true): HtmlString
    {
        $text = trim((string) $text);
        $end = '';
        if ($accentEnd && preg_match('/[.?!]$/u', $text) && ! str_ends_with($text, '^'.mb_substr($text, -1))) {
            $end = '<span class="dot">'.e(mb_substr($text, -1)).'</span>';
            $text = mb_substr($text, 0, -1);
        }

        $html = preg_replace('/\^([.?!])/u', '<span class="dot">$1</span>', e($text));

        return new HtmlString(nl2br($html, false).$end);
    }
}
