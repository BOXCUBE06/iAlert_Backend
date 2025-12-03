<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // OPTIMIZATION: 'login_id' stores student_id or email
            $table->string('login_id')->unique(); 
            
            $table->string('password');
            
            // DE-NORMALIZATION: Storing Name and Phone here avoids JOINs during emergency alerts.
            $table->string('name'); 
            $table->string('phone_number')->nullable();
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->enum('role', ['admin', 'student', 'responder'])->index();
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }

};
