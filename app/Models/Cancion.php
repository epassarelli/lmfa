<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Interprete;
use App\Models\Album;
use App\Models\User;

class Cancion extends Model
{
    use HasFactory;
    protected $table = 'canciones';

    public function albunes()
    {
        return $this->belongsToMany(Album::class, 'albunes_canciones');
    }

    public function interprete()
    {
        return $this->belongsTo(Interprete::class, 'interprete_id');
        // return $this->belongsTo(Interprete::class, 'interpretes_canciones');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
