<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // OPTIMIZATION: One column for ALL user types
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Context: Link to the specific alert (so clicking it opens the map)
            $table->foreignId('alert_id')->nullable()->constrained('alerts')->cascadeOnDelete();

            $table->string('title');   // e.g., "New Emergency Alert!"
            $table->text('message');   // e.g., "Student John is asking for help."
            $table->string('type')->default('info'); // info, alert, success

            // Standard mechanism for "Mark as Read"
            $table->timestamp('read_at')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};