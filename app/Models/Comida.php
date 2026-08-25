<?php

namespace App\Models;

use App\Traits\CommonMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comida extends Model
{
    use CommonMethodsTrait;
    use HasFactory;

    protected $fillable = [
        'titulo',
        'receta',
        'foto',
        'slug',
        'publicar',
        'visitas',
        'estado',
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
