<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    // Disable updated_at, logs should never be changed, only created
    public $timestamps = false; 

    protected $fillable = ['user_id', 'action', 'details','created_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
