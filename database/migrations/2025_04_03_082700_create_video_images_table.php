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
        Schema::create('video_images', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->default(0);
            $table->string('path')->nullable();
            $table->string('alt')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_images');
    }
};
