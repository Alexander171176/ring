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
        Schema::table('tournaments', function (Blueprint $table) {
            $table->boolean('left')->default(false)->index()->after('activity');
            $table->boolean('main')->default(false)->index()->after('left');
            $table->boolean('right')->default(false)->index()->after('main');
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn(['left', 'main', 'right']);
        });
    }
};
