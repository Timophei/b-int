<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Блоки по мобильному макету поддержки: «Сначала стабильность. Затем развитие.»,
 * «Как работаем.» и ссылка на PDF-оффер в карточке тарифа. Тексты из макета
 * подставляются в раздел «Поддержка», только если поля ещё пустые.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->string('highlight_title')->nullable()->after('body');
            $table->string('highlight_text', 500)->nullable()->after('highlight_title');
            $table->string('process_title')->nullable()->after('highlight_text');
            $table->json('process')->nullable()->after('process_title');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('offer_pdf')->nullable()->after('note');
            $table->string('offer_pdf_label', 60)->nullable()->after('offer_pdf');
        });

        DB::table('sections')->where('key', 'support')->whereNull('highlight_title')->update([
            'highlight_title' => "Сначала стабильность.\nЗатем развитие.",
            'highlight_text' => 'Если после обслуживания остаётся время — улучшаем портал под ваши задачи.',
            'process_title' => 'Как работаем.',
            'process' => json_encode([
                'Регулярные регламентные работы.',
                'Прорисовываем схемы ваших процессов и сценариев.',
                'Оперативно реагируем на проблемы: в переписке, в экстренных случаях — голосом.',
                'Составляем рекомендации по апгрейдам и консультируем по доработкам.',
            ], JSON_UNESCAPED_UNICODE),
        ]);

        // В меню по макету: Поддержка, ИИ сотрудники, Битрикс24, О нас, Курсы — курсы в конец
        DB::table('sections')->where('key', 'courses')->where('sort', 3)->update(['sort' => 5]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['offer_pdf', 'offer_pdf_label']);
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['highlight_title', 'highlight_text', 'process_title', 'process']);
        });
    }
};
