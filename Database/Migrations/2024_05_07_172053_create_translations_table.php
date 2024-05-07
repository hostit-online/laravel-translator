<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONFIG_KEY = 'laravel-translation';

    public function up(): void
    {
        $translationsTable = config(sprintf('%s.translation_table', self::CONFIG_KEY));
        Schema::create($translationsTable, static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('translatable_id');
            $table->string('translatable_type');
            $table->string('iso_code');
            $table->string('value');
            $table->string('translatable_column');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $translationsTable = config(sprintf('%s.translation_table', self::CONFIG_KEY));

        Schema::dropIfExists($translationsTable);
    }
};
