<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Interprete;
use App\Models\User;

class Show extends Model
{
    use HasFactory;
    protected $fillable = ['show', 'slug', 'detalle', 'foto', 'visitas', 'publicar', 'user_id', 'estado', 'interprete_id', 'fecha', 'hora', 'lugar', 'direccion'];

    public function interprete()
    {

        return $this->belongsTo(Interprete::class);
        // return $this->belongsToMany(Interprete::class, 'interpretes_shows');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
