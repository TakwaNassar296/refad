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
            $table->dropColumn(['hero_title', 'hero_description', 'hero_image', 'hero_subtitle','small_hero_image']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepages', function (Blueprint $table) {
            $table->string('hero_title');
            $table->text('hero_description');
            $table->string('hero_image')->nullable();
            $table->string('small_hero_image')->nullable();
            $table->string('hero_subtitle')->nullable();
        });
    }
};
