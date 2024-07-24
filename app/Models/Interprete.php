<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Noticia;
use App\Models\Show;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\User;

use App\Traits\CommonMethodsTrait;

class Interprete extends Model
{
    use CommonMethodsTrait;

    use HasFactory;
    // protected $fillable = ['interprete', 'slug', 'biografia', 'foto', 'visitas', 'publicar', 'user_id', 'estado'];
    protected $fillable = [
        'interprete', 'slug', 'biografia', 'foto', 'telefono', 'correo',
        'instagram', 'twitter', 'youtube', 'visitas', 'estado', 'user_id'
    ];

    // Retorna una coleccion con interpretes menos el actual
    public static function getInterpretesExcluding($currentInterpreteId)
    {
        // return self::where('id', '!=', $currentInterpreteId)->get();
        return self::where('id', '!=', $currentInterpreteId)
            ->where('estado', 1)
            ->orderBy('interprete', 'asc')
            ->get();
    }

    // Definir un scope para filtrar intérpretes activos y ordenarlos por el campo 'interprete'
    public function scopeActive($query)
    {
        return $query->where('estado', 1)
            ->orderBy('interprete', 'asc')
            ->select('id', 'interprete', 'slug');
    }

    public function noticias()
    {
        return $this->hasMany(Noticia::class);
    }

    public function shows()
    {
        return $this->hasMany(Show::class);
    }

    public function discos()
    {
        return $this->hasMany(Album::class);
    }

    public function canciones()
    {
        return $this->hasMany(Cancion::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
