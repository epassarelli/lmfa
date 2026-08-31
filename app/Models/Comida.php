<?php

namespace App\Models;

use App\Traits\CommonMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comida extends Model
{
    use CommonMethodsTrait;
    use HasFactory;

    protected $casts = [
        'ingredients' => 'array',
        'instructions' => 'array',
        'publicar' => 'datetime',
        'visitas' => 'integer',
        'estado' => 'integer',
        'prep_time_minutes' => 'integer',
        'cook_time_minutes' => 'integer',
    ];

    protected $fillable = [
        'titulo',
        'slug',
        'receta',
        'excerpt',
        'ingredients',
        'instructions',
        'prep_time_minutes',
        'cook_time_minutes',
        'servings',
        'region',
        'seo_title',
        'meta_description',
        'foto',
        'image_alt',
        'publicar',
        'visitas',
        'estado',
        'user_id',
    ];

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
