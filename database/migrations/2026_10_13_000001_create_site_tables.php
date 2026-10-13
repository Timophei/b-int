<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Содержимое сайта: разделы (каждый — своя страница), их услуги, тарифы и шаги,
 * заявки с формы и общие настройки (контакты, счётчики).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique(); // home, support, ai, courses, contact, privacy — по нему сайт узнаёт раздел
            $table->string('path', 120)->unique(); // адрес без слэшей по краям; у главной — пусто
            $table->string('nav_label', 60);
            $table->boolean('in_nav')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('external_url')->nullable(); // раздел живёт на другом сайте (курсы)
            $table->string('eyebrow')->nullable(); // мелкая строка над заголовком
            $table->string('title'); // крупный заголовок; точка в конце подсвечивается
            $table->string('subtitle')->nullable();
            $table->string('lead', 500)->nullable();
            $table->string('teaser')->nullable(); // строка о разделе на главной
            $table->longText('body')->nullable();
            $table->string('cover')->nullable(); // картинка шапки; пусто — фирменная ночная гладь
            $table->string('steps_title')->nullable();
            $table->string('cta_label')->nullable();
            $table->boolean('is_published')->default(true);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->string('seo_keywords', 500)->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description', 500)->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('description', 500)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('price', 60); // «25 000 ₽», «от 40 000 ₽», «по запросу»
            $table->string('period', 60)->nullable(); // «/ месяц»
            $table->json('features')->nullable();
            $table->string('button_label', 60)->nullable();
            $table->string('note', 500)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('text', 500)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('contact', 190); // телефон, почта или ник в мессенджере
            $table->string('company', 190)->nullable();
            $table->text('message')->nullable();
            $table->string('topic', 40)->nullable(); // из какого раздела пришли: support, ai…
            $table->string('page', 255)->nullable();
            $table->string('source', 20)->nullable();
            $table->string('source_name', 100)->nullable();
            $table->string('utm_source', 150)->nullable();
            $table->string('utm_campaign', 150)->nullable();
            $table->string('status', 20)->default('new'); // new, work, done, spam
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Байкал Интеграция');
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('telegram', 120)->nullable();
            $table->string('whatsapp', 40)->nullable();
            $table->string('max_link')->nullable();
            $table->string('notify_email')->nullable(); // куда слать заявки
            $table->string('requisites', 1000)->nullable();
            $table->string('metrika_id', 20)->nullable();
            $table->text('head_code')->nullable(); // проверочные метатеги Яндекса/Google и т.п.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'leads', 'steps', 'plans', 'services', 'sections'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
