<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_has_video', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tournament_id')
                ->constrained('tournaments')
                ->onDelete('cascade');

            $table->foreignId('video_id')
                ->constrained('videos')
                ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['tournament_id', 'video_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_has_video');
    }
};
