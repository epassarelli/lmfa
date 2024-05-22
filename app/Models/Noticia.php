<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Interprete;
use App\Models\User;

class Noticia extends Model
{
    use HasFactory;

    protected $fillable = ['titulo', 'slug', 'noticia', 'foto', 'visitas', 'publicar', 'user_id', 'estado'];


    public function interprete()
    {
        return $this->belongsTo(Interprete::class, 'interprete_id');
        // return $this->belongsToMany(Interprete::class, 'interpretes_noticias');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
