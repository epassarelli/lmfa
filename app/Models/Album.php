<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Interprete;
use App\Models\Cancion;
use App\Models\User;

class Album extends Model
{
    use HasFactory;
    protected $table = 'albunes';

    public function interprete()
    {
        return $this->belongsTo(Interprete::class, 'interprete_id');
        // return $this->belongsToMany(Interprete::class, 'interpretes_albunes');
    }

    public function canciones()
    {
        return $this->belongsToMany(Cancion::class, 'albunes_canciones');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
