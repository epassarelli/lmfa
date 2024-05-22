<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Festival extends Model
{
    use HasFactory;
    protected $table = 'festivales';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
