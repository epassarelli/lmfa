<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Interprete;
use App\Models\Album;
use App\Models\User;
use App\Traits\CommonMethodsTrait;

class Cancion extends Model
{
    use CommonMethodsTrait;
    use HasFactory;
    protected $table = 'canciones';
    protected $fillable = [
        'cancion', 'slug', 'letra', 'youtube', 'spotify', 'visitas', 'publicar', 'estado', 'user_id', 'interprete_id'
    ];


    public function albunes()
    {
        return $this->belongsToMany(Album::class, 'albunes_canciones');
    }

    public function interprete()
    {
        return $this->belongsTo(Interprete::class, 'interprete_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
