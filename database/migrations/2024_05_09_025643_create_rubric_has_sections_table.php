<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rubric_has_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rubric_id');
            $table->unsignedBigInteger('section_id');

            // Внешние ключи
            $table->foreign('rubric_id')->references('id')->on('rubrics')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');

            // Комбинация должна быть уникальной
            $table->unique(['rubric_id', 'section_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubric_has_sections');
    }
};
