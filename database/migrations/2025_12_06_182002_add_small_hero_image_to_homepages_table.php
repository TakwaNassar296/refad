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
        Schema::table('homepages', function (Blueprint $table) {
            $table->string('small_hero_image')->nullable()->after('hero_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepages', function (Blueprint $table) {
            $table->dropColumn('small_hero_image');
        });
    }
};
