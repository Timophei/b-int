<?php

use Database\Seeders\Content\AiEmployeesContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Блоки страницы «ИИ сотрудники» по макету: примеры-сценарии с иконками и метками,
 * подписи разделов страницы, кнопка в шапке, промежуточный и финальный призывы,
 * иконки шагов, разовая настройка и ежемесячные тарифы.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->string('hero_button', 60)->nullable()->after('lead');
            $table->string('cases_kicker', 60)->nullable()->after('process');
            $table->string('cases_title')->nullable()->after('cases_kicker');
            $table->string('services_kicker', 60)->nullable()->after('cases_title');
            $table->string('services_title')->nullable()->after('services_kicker');
            $table->string('steps_kicker', 60)->nullable()->after('steps_title');
            $table->string('plans_title')->nullable()->after('steps_kicker');
            $table->string('plans_note', 500)->nullable()->after('plans_title');
            $table->string('band_title')->nullable()->after('plans_note');
            $table->string('band_text', 500)->nullable()->after('band_title');
            $table->string('final_title')->nullable()->after('band_text');
            $table->string('final_text', 500)->nullable()->after('final_title');
            $table->string('final_button', 60)->nullable()->after('final_text');
        });

        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('icon', 20)->nullable();
            $table->string('title');
            $table->string('tag', 100)->nullable(); // у кого работает: «Город чая»
            $table->string('text', 500)->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_label', 100)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::table('steps', function (Blueprint $table) {
            $table->string('icon', 20)->nullable()->after('text');
        });

        Schema::table('plans', function (Blueprint $table) {
            // main — карточка рядом с услугами, setup — разовая настройка, monthly — ежемесячные тарифы
            $table->string('group', 20)->default('main')->after('section_id');
        });

        AiEmployeesContent::applyIfPlaceholder();
    }

    public function down(): void
    {
        Schema::table('plans', fn (Blueprint $table) => $table->dropColumn('group'));
        Schema::table('steps', fn (Blueprint $table) => $table->dropColumn('icon'));
        Schema::dropIfExists('cases');
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['hero_button', 'cases_kicker', 'cases_title', 'services_kicker', 'services_title', 'steps_kicker',
                'plans_title', 'plans_note', 'band_title', 'band_text', 'final_title', 'final_text', 'final_button']);
        });
    }
};
