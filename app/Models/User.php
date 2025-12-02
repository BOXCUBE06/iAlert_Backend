<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'login_id', // Stores either Student ID or Email
        'name',
        'phone_number', // Moved here for optimization
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relationship: Vertical Partitioning
    public function studentDetails()
    {
        return $this->hasOne(StudentDetails::class);
    }

    public function responderDetails()
    {
        return $this->hasOne(ResponderDetails::class);
    }

    public function alertsCreated()
    {
        return $this->hasMany(Alert::class, 'student_id');
    }

    // 2. Get all alerts accepted by this responder
    public function alertsResponded()
    {
        return $this->hasMany(Alert::class, 'responder_id');
    }

    // In App\Models\User.php

public function notifications()
{
    return $this->hasMany(Notification::class);
}
}
