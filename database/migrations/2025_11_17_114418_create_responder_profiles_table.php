<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responder_details', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key linking to the main User
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->string('position');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responder_details');
    }
};
