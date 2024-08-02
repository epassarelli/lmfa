<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Interprete;
use App\Models\Cancion;
use App\Models\User;
use App\Traits\CommonMethodsTrait;

class Album extends Model
{
    use CommonMethodsTrait;
    use HasFactory;
    protected $table = 'albunes';
    protected $fillable = [
        'album', 'slug', 'anio', 'foto', 'spotify', 'visitas', 'estado', 'user_id', 'interprete_id'
    ];

    public function interprete()
    {
        return $this->belongsTo(Interprete::class, 'interprete_id');
    }

    public function canciones()
    {
        return $this->belongsToMany(Cancion::class, 'albunes_canciones')
            ->withPivot('orden')
            ->orderBy('pivot_orden');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
