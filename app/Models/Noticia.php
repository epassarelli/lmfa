<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Interprete;
use App\Models\User;
use App\Traits\CommonMethodsTrait;

class Noticia extends Model
{
    use CommonMethodsTrait;
    use HasFactory;

    protected $fillable = ['titulo', 'slug', 'noticia', 'interprete_id', 'foto', 'visitas', 'publicar', 'user_id', 'estado'];


    public function interprete()
    {
        return $this->belongsTo(Interprete::class, 'interprete_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
