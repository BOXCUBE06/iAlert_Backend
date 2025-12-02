<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDetails extends Model
{
    protected $fillable = [
        'user_id',
        'department',
        'year_level',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
