<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['nombre', 'slug', 'foto', 'metetittle', 'metadescription', 'status'];

    protected $attributes = [
        'metetittle' => '',
        'metadescription' => '',
    ];

    public function noticias()
    {
        return $this->hasMany(News::class);
    }
}
