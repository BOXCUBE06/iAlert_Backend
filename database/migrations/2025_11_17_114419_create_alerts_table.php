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
            
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();;
            
            $table->foreignId('responder_id')->nullable()->constrained('users')->cascadeOnDelete();;

            $table->string('student_name'); 
            $table->string('student_phone'); 

            // ALERT DETAILS
            $table->string('category'); // Medical, Fire, Harassment
            $table->enum('severity', ['severe', 'moderate', 'mild']);
            $table->text('description')->nullable();
            
            // LOCATION (Current Position)
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // STATUS and TIMESTAMPS
            // 'pending' -> 'accepted' -> 'arrived' -> 'resolved'
            $table->enum('status', ['pending', 'accepted', 'arrived', 'resolved', 'cancelled'])
                  ->default('pending')
                  ->index();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
