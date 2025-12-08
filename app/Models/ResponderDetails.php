<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponderDetails extends Model
{
    protected $fillable = [
        'user_id',
        'position',
        // --- ADD THESE NEW COLUMNS ---
        'is_online',
        'current_latitude',
        'current_longitude',
        'last_seen_at',
    ];

    protected $casts = [
        'is_online' => 'boolean', // Auto-converts 0/1 to true/false
        'last_seen_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}