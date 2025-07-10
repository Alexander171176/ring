<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            if (!Schema::hasColumn('tournaments', 'url')) {
                $table->string('url', 500)->nullable()->index()->after('name');
            }
            if (!Schema::hasColumn('tournaments', 'meta_title')) {
                $table->string('meta_title', 255)->nullable()->after('time_of_finish');
            }
            if (!Schema::hasColumn('tournaments', 'meta_keywords')) {
                $table->string('meta_keywords', 255)->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('tournaments', 'meta_desc')) {
                $table->text('meta_desc')->nullable()->after('meta_keywords');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            if (Schema::hasColumn('tournaments', 'url')) {
                $table->dropColumn('url');
            }
            if (Schema::hasColumn('tournaments', 'meta_title')) {
                $table->dropColumn('meta_title');
            }
            if (Schema::hasColumn('tournaments', 'meta_keywords')) {
                $table->dropColumn('meta_keywords');
            }
            if (Schema::hasColumn('tournaments', 'meta_desc')) {
                $table->dropColumn('meta_desc');
            }
        });
    }
};
