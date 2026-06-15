<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Provincia extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    public function getSlugAttribute(): string
    {
        return Str::slug($this->nombre);
    }
}
