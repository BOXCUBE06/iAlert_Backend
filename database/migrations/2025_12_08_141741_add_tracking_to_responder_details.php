<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responder_details', function (Blueprint $table) {
            // Toggle for "On Duty"
            $table->boolean('is_online')->default(false)->after('position');
            
            // Live GPS coordinates
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            
            // The "Pulse" - Used to auto-hide responders if their phone dies
            $table->timestamp('last_seen_at')->nullable(); 
        });
    }

    public function down(): void
    {
        Schema::table('responder_details', function (Blueprint $table) {
            $table->dropColumn(['is_online', 'current_latitude', 'current_longitude', 'last_seen_at']);
        });
    }
};