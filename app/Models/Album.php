<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;
    protected $table = 'albunes';

    public function interpretes()
    {
        return $this->belongsToMany(Interprete::class, 'interprete_album');
    }

    public function canciones()
    {
        return $this->belongsToMany(Cancion::class, 'album_cancion');
    }
}
