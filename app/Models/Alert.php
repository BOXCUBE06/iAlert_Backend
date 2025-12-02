<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'student_id',
        'responder_id',    // Moved here
        'student_name',    // Snapshot
        'student_phone',   // Snapshot
        'category',
        'severity',
        'description',
        'latitude',
        'longitude',
        'status',
        'responded_at',    // Moved here
        'arrived_at',      // Moved here
        'resolved_at',     // Renamed from completed_at
    ];

    // OPTIMIZATION: "Casts" automatically convert timestamps to Carbon objects
    protected $casts = [
        'latitude'     => 'float',
        'longitude'    => 'float',
        'responded_at' => 'datetime',
        'arrived_at'   => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    // Relationship: Who called for help?
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Relationship: Who accepted it?
    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    // Relationship: The location trail
    public function locationHistory()
    {
        return $this->hasMany(LocationHistory::class);
    }
}