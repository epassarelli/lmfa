<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;
    protected $table = 'albunes';

    public function interprete()
    {
        return $this->belongsTo(Interprete::class);
    }

    public function canciones()
    {
        return $this->belongsToMany(Cancion::class, 'album_cancion');
    }
}
