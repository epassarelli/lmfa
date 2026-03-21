<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $table = 'contact_messages'; // Assuming this table name based on conventions, user said "La tabla ya existe"
    // If the table is different, we might need to change this.
    // Fields: id, nombre, email, asunto, mensaje, fecha_envio

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'email',
        'asunto',
        'mensaje',
        'fecha_envio',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
    ];
}
