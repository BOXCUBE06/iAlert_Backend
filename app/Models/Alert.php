<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'student_id',
        'responder_id',    
        'student_name',    
        'student_phone',   
        'category',
        'severity',
        'description',
        'latitude',
        'longitude',
        'status',
        'responded_at',    
        'arrived_at',      
        'resolved_at',     
    ];

    protected $casts = [
        'latitude'     => 'float',
        'longitude'    => 'float',
        'responded_at' => 'datetime',
        'arrived_at'   => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function locationHistory()
    {
        return $this->hasMany(LocationHistory::class);
    }
}