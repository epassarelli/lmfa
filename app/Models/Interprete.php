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
        return $this->belongsToMany(Noticia::class, 'interprete:noticia');
    }

    public function shows()
    {
        return $this->belongsToMany(Show::class, 'interprete:show');
    }

    public function albunes()
    {
        return $this->belongsToMany(Album::class, 'interprete:album');
    }

    public function canciones()
    {
        return $this->belongsToMany(Cancion::class, 'interprete:cancion');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
