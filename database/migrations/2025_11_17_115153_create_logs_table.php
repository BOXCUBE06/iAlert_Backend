<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Who performed the action?
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('action'); // e.g., "LOGIN", "ALERT_CREATED", "ALERT_RESOLVED"
            $table->text('details')->nullable(); // e.g., "Changed status to Arrived"

            $table->timestamp('created_at')->useCurrent()->index(); // Index for sorting
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
