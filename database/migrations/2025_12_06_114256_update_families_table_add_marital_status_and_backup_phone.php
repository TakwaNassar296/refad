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
        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn(['children_count', 'elderly_count', 'medical_conditions_count']);

            $table->string('backup_phone')->nullable()->after('phone');
            $table->foreignId('marital_status_id')->nullable()->after('backup_phone')
                  ->constrained('marital_statuses')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->integer('children_count')->default(0);
            $table->integer('elderly_count')->default(0);
            $table->integer('medical_conditions_count')->default(0);

            $table->dropForeign(['marital_status_id']);
            $table->dropColumn('marital_status_id');
            $table->dropColumn('backup_phone');
        });
    }
};
