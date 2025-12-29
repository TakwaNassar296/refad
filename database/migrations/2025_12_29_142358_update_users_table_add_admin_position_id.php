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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('camp_name');
            $table->foreignId('admin_position_id')->nullable()->after('role')->constrained('admin_positions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('camp_name')->nullable()->after('role');
            $table->dropForeign(['admin_position_id']);
            $table->dropColumn('admin_position_id');
        });
    }
};
