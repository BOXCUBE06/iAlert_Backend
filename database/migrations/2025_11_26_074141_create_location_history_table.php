<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_history', function (Blueprint $table) {
            $table->id();
            
            // Link to the main alert
            $table->foreignId('alert_id')
                  ->constrained('alerts')
                  ->cascadeOnDelete();
            
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            $table->timestamp('created_at')->useCurrent()->index(); 
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_history');
    }
};