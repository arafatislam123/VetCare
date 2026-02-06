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
        Schema::create('service_veterinarian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('veterinarian_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['service_id', 'veterinarian_id'], 'service_vet_unique');
            $table->index('service_id');
            $table->index('veterinarian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_veterinarian');
    }
};
