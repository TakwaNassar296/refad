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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_id')->constrained('camps')->cascadeOnDelete();
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');                        
            $table->string('type')->nullable();            
            $table->unsignedInteger('beneficiary_count')->default(0); 
            $table->string('college')->nullable();         
            $table->enum('status', ['pending', 'in_progress', 'delivered', 'cancelled'])->default('pending');  
            $table->text('notes')->nullable(); 
            $table->string('project_image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
