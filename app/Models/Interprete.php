<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Noticia;
use App\Models\Show;
use App\Models\Album;
use App\Models\Cancion;

class Interprete extends Model
{
    use HasFactory;
    protected $fillable = ['interprete', 'slug', 'biografia', 'foto', 'visitas', 'publicar', 'user_id', 'estado'];

    public function noticias()
    {
        return $this->hasMany(Noticia::class);
    }

    public function shows()
    {
        return $this->hasMany(Show::class);
    }

    public function albunes()
    {
        return $this->hasMany(Album::class);
    }

    public function canciones()
    {
        return $this->hasMany(Cancion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
