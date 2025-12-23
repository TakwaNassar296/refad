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
        Schema::table('family_members', function (Blueprint $table) {
           $table->dropUnique(['national_id']);
           $table->unique(['national_id', 'deleted_at'], 'family_members_national_id_deleted_at_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            $table->dropUnique('family_members_national_id_deleted_at_unique');
            $table->unique('national_id');
        });
    }
};
