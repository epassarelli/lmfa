<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'province_id',
        'city',
        'latitude',
        'longitude',
        'phone',
        'website',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'province_id');
    }
}
