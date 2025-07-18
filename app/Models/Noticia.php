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

    protected $fillable = ['titulo', 'categoria_id', 'slug', 'noticia', 'interprete_id', 'foto', 'visitas', 'publicar', 'user_id', 'estado'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    
    public function interprete()
    {
        return $this->belongsTo(Interprete::class);
    }

    public function interpretes()
    {
        return $this->belongsToMany(Interprete::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUrl(): string
    {
        if ($this->interprete) {
            return route('artista.noticia', [
                'interprete' => $this->interprete->slug,
                'noticia' => $this->slug
            ]);
        }

        return route('noticias.show', ['slug' => $this->slug]);
    }
}
