<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Interprete;
use App\Models\Album;

class Cancion extends Model
{
    use HasFactory;
    protected $table = 'canciones';

    public function albunes()
    {
        return $this->belongsToMany(Cancion::class, 'album_cancion');
    }

    public function interpretes()
    {
        return $this->belongsToMany(Interprete::class, 'interprete_cancion');
    }

    public function interprete()
    {
        return $this->belongsTo(Interprete::class);
    }
}
