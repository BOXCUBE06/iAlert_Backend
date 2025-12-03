<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationHistory extends Model
{
    protected $table = 'location_history';

    protected $fillable = [
        'alert_id',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }
}