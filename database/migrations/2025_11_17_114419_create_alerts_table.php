<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/xxxx_create_alerts_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            
            // --- RELATIONSHIPS ---
            $table->foreignId('student_id')->constrained('users');
            
            // OPTIMIZATION: Move responder_id HERE. 
            // We don't want a separate table. We want to know "Who accepted this?" instantly.
            $table->foreignId('responder_id')->nullable()->constrained('users');

            // --- DE-NORMALIZATION (Snapshot) ---
            // Store name/phone here so we don't need to JOIN the users table for the Map.
            $table->string('student_name'); 
            $table->string('student_phone'); 

            // --- ALERT DETAILS ---
            $table->string('category'); // Medical, Fire, Harassment
            $table->enum('severity', ['severe', 'moderate', 'mild']);
            $table->text('description')->nullable();
            
            // --- LOCATION (Current Position) ---
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // --- STATUS & TIMESTAMPS ---
            // 'pending' -> 'accepted' -> 'arrived' -> 'resolved'
            $table->enum('status', ['pending', 'accepted', 'arrived', 'resolved', 'cancelled'])
                  ->default('pending')
                  ->index(); // INDEX this! Admin queries "WHERE status = 'pending'" constantly.

            $table->timestamp('created_at')->useCurrent(); // When student pressed button
            $table->timestamp('responded_at')->nullable(); // When responder clicked "Accept"
            $table->timestamp('arrived_at')->nullable();   // When responder clicked "I'm here"
            $table->timestamp('resolved_at')->nullable();  // When finished
            
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
