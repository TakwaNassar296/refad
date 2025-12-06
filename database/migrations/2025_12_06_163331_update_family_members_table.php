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

            $table->dropColumn('status');

            $table->foreignId('relationship_id')
                ->nullable()
                ->after('family_id')
                ->constrained('relationships')
                ->nullOnDelete();

            $table->foreignId('medical_condition_id')
                ->nullable()
                ->after('relationship_id')
                ->constrained('medical_conditions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {

            $table->string('status')->nullable();

            $table->dropForeign(['relationship_id']);
            $table->dropColumn('relationship_id');

            $table->dropForeign(['medical_condition_id']);
            $table->dropColumn('medical_condition_id');
        });
    }
};
