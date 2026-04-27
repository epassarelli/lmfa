<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use HasFactory;

    protected $table = 'media_assets';

    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'disk',
        'original_name',
        'original_path',
        'original_width',
        'original_height',
        'mime',
        'size',
        'alt',
        'caption',
        'group',
        'sort_order',
        'profile',
        'created_by',
        'variants_json',
    ];

    protected $casts = [
        'variants_json' => 'array',
        'size' => 'integer',
        'original_width' => 'integer',
        'original_height' => 'integer',
        'sort_order' => 'integer',
    ];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Resuelve una ruta o URL a una URL pública completa.
     * Soporta registros viejos (URL absoluta con dominio cualquiera)
     * y nuevos registros (ruta relativa al disco).
     */
    private function resolveStorageUrl(?string $pathOrUrl): string
    {
        if (!$pathOrUrl) return '';

        // Legacy: URL absoluta (puede tener dominio equivocado) → extraer ruta relativa
        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            if (preg_match('#/storage/(.+)$#', $pathOrUrl, $matches)) {
                return Storage::disk($this->disk ?? 'public')->url($matches[1]);
            }
            return $pathOrUrl; // URL externa desconocida, devolver tal cual
        }

        return Storage::disk($this->disk ?? 'public')->url($pathOrUrl);
    }

    /**
     * original_path resuelto a URL pública con el dominio actual.
     */
    public function getOriginalPathAttribute(): ?string
    {
        $raw = $this->attributes['original_path'] ?? null;
        return $raw ? $this->resolveStorageUrl($raw) : null;
    }

    /**
     * variants_json con todas las URLs resueltas al dominio actual.
     */
    public function getVariantsJsonAttribute($value): array
    {
        $data = is_array($value) ? $value : (json_decode($value ?? '[]', true) ?? []);
        $resolved = [];
        foreach ($data as $variantName => $sizes) {
            $resolved[$variantName] = [];
            foreach ((array) $sizes as $width => $path) {
                $resolved[$variantName][$width] = $this->resolveStorageUrl((string) $path);
            }
        }
        return $resolved;
    }

    /**
     * Alias: $asset->path → original_path raw (sin resolver, para operaciones de Storage).
     */
    public function getPathAttribute(): ?string
    {
        return $this->attributes['original_path'] ?? null;
    }

    public function setPathAttribute($value): void
    {
        $this->attributes['original_path'] = $value;
    }
}
