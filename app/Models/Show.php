<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use App\Models\Interprete;
use App\Models\Provincia;
use App\Models\User;

use App\Traits\CommonMethodsTrait;

class Show extends Model
{
    protected $table = 'shows';

    protected $fillable = [
        'show',
        'detalles',
        'fecha',
        'hora',
        'lugar',
        'direccion',
        'interprete_id',
        'precio_entrada',
        'link_entradas',
        'destacado',
        'imagen_destacada',
        'slug',
        'lat',
        'lng',
        'provincia_id',
    ];

    // Relaciones
    public function interprete()
    {
        return $this->belongsTo(Interprete::class);
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mutator para slug automático
    public static function boot()
    {
        parent::boot();

        static::creating(function ($show) {
            if (empty($show->slug)) {
                $show->slug = Str::slug($show->show . '-' . now()->timestamp);
            }
        });
    }
}
