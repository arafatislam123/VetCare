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
        Schema::create('veterinarian_specialization', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veterinarian_id')->constrained()->onDelete('cascade');
            $table->foreignId('specialization_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['veterinarian_id', 'specialization_id'], 'vet_spec_unique');
            $table->index('veterinarian_id');
            $table->index('specialization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veterinarian_specialization');
    }
};
