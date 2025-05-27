<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Traits\CommonMethodsTrait;

class Mito extends Model
{
    use CommonMethodsTrait;
    use HasFactory;

    protected $fillable = [
        'titulo',
        'mito',
        'foto',
        'slug',
        'publicar',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
