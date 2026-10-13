<?php

namespace Tests\Unit;

use App\Support\Typo;
use PHPUnit\Framework\TestCase;

class TypoTest extends TestCase
{
    public function test_final_punctuation_and_marked_dot_are_accented(): void
    {
        $this->assertSame('Кто<span class="dot">?</span>', (string) Typo::title('Кто?'));
        $this->assertSame("Ваши знания<span class=\"dot\">.</span><br>\nЕго материал<span class=\"dot\">.</span>", (string) Typo::title("Ваши знания^.\nЕго материал."));
        $this->assertSame('Без точки.', (string) Typo::title('Без точки.', false));
        $this->assertSame('&lt;b&gt;', (string) Typo::title('<b>'));
    }
}
