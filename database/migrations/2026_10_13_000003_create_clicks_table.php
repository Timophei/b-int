<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Нажатия на почту, телефон, мессенджеры, кнопки «Обсудить» и переходы на сайт курсов.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->index();
            $table->string('kind', 20); // email, phone, telegram, whatsapp, max, cta, external
            $table->string('label', 150)->nullable();
            $table->string('path', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
