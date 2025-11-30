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
        Schema::create('delegate_families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained('families')->cascadeOnDelete();
            $table->foreignId('contribution_id')->constrained('contributions')->cascadeOnDelete();
            $table->integer('received_quantity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['family_id', 'contribution_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegate_families');
    }
};
