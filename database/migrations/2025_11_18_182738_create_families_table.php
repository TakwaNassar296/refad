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
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_id')->constrained()->onDelete('cascade');
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->string('family_name'); 
            $table->string('national_id')->unique(); 
            $table->date('dob')->nullable();    
            $table->string('phone')->nullable();         
            $table->integer('total_members')->default(0);    
            $table->integer('elderly_count')->default(0);
            $table->integer('medical_conditions_count')->default(0); 
            $table->integer('children_count')->default(0); 
            $table->string('tent_number')->nullable(); 
            $table->string('location')->nullable();  
            $table->text('notes')->nullable();  
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};


