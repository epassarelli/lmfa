<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classified extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'location',
        'contact_info',
        'contact_whatsapp',
        'expiration_date',
        'is_active',
        'is_featured',
        'estado',
        'moderator_comment',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'is_active'       => 'boolean',
        'is_featured'     => 'boolean',
    ];

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'classified_tag');
    }

    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
