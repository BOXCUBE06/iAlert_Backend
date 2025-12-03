<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'login_id',
        'name',
        'phone_number',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function alertsResponded()
    {
        return $this->hasMany(Alert::class, 'responder_id');
    }

public function notifications()
{
    return $this->hasMany(Notification::class);
}
}
