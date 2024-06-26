<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Traits\CommonMethodsTrait;

class Festival extends Model
{
    use CommonMethodsTrait;
    use HasFactory;
    protected $table = 'festivales';

    protected $fillable = [
        'titulo',
        'slug',
        'detalle',
        'foto',
        'provincia_id',
        'mes_id',
        'user_id',
        'visitas',
        'publicar',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function mes()
    {
        return $this->belongsTo(Mes::class);
    }
}
