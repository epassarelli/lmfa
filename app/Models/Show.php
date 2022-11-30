<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Interprete;

class Show extends Model
{
    use HasFactory;

    public function interpretes()
    {
        return $this->belongsToMany(Interprete::class, 'interprete_shows');
    }
}
