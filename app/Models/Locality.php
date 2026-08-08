<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Locality extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id',
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $locality) {
            if (blank($locality->slug) && filled($locality->name)) {
                $locality->slug = Str::slug($locality->name);
            }
        });
    }

    public function province()
    {
        return $this->belongsTo(Provincia::class, 'province_id');
    }
}
