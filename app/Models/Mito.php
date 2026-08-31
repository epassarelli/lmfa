<?php

namespace App\Models;

use App\Traits\CommonMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mito extends Model
{
    use CommonMethodsTrait;
    use HasFactory;

    protected $casts = [
        'publicar' => 'datetime',
        'visitas' => 'integer',
        'estado' => 'integer',
    ];

    protected $fillable = [
        'titulo',
        'content_type',
        'slug',
        'mito',
        'excerpt',
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
