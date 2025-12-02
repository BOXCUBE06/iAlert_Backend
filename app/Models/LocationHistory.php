<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationHistory extends Model
{
    // Explicitly define the table name just in case
    protected $table = 'location_history';

    protected $fillable = [
        'alert_id',
        'latitude',
        'longitude',
        // 'created_at' is automatically handled by Laravel timestamps
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        // No need to cast created_at, Laravel does it by default
    ];

    /**
     * Relationship: This breadcrumb belongs to one specific Alert.
     */
    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }
}