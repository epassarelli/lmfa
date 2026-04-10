<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_type',
        'provider',
        'variant_name',
        'template_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Render the template by replacing tokens.
     *
     * Supports: {title}, {excerpt}, {url}, {date}, {subtitle}
     */
    public function render(array $tokens): string
    {
        $text = $this->template_text;

        foreach ($tokens as $key => $value) {
            $text = str_replace("{{$key}}", $value, $text);
        }

        return $text;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }
}
