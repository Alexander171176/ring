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
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(0);
            $table->text('icon')->nullable();
            $table->string('name')->unique();
            $table->string('version')->nullable();
            $table->string('code')->nullable();
            $table->text('options')->nullable();
            $table->text('description')->nullable();
            $table->text('readme')->nullable();
            $table->string('templates')->nullable();
            $table->boolean('activity')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
