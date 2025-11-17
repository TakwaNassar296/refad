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
            $table->string('id_number')->unique();
            $table->string('phone');
            $table->enum('role', ['admin', 'delegate', 'contributor'])->default('delegate');
            $table->string('license_number')->nullable();
            $table->string('admin_position')->nullable();
            $table->boolean('accept_terms')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('reset_code', 6)->nullable();
            $table->timestamp('reset_code_expires_at')->nullable();
            $table->string('backup_phone')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'id_number',
                'phone',
                'role',
                'license_number',
                'accept_terms',
                'status',
                'admin_position',
                'reset_code',
                'reset_code_expires_at',
                'backup_phone'
            ]);
        });
    }
};
