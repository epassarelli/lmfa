<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\Image;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImageUploadService
{
    protected $manager;

    public function __construct()
    {
        // Usamos el driver GD por defecto. 
        // Se podría cambiar a Imagick si está disponible.
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Procesa una imagen, genera variantes y la guarda en la base de datos.
     */
    public function process(
        UploadedFile $file,
        string $profile,
        string $folder,
        ?string $filename = null
    ): array {
        $config = config("image_profiles.{$profile}");

        if (!$config) {
            throw new \InvalidArgumentException("Perfil de imagen '{$profile}' no encontrado.");
        }

        $now = Carbon::now();
        $datePath = $now->format('Y/m');
        $basePath = "public/{$folder}/{$datePath}";
        
        // Asegurar que el directorio existe
        Storage::makeDirectory($basePath);

        $filename = $filename ?: Str::random(20);
        $originalExtension = $file->getClientOriginalExtension();
        $originalName = "{$filename}_original.{$originalExtension}";
        $originalPath = "{$basePath}/{$originalName}";

        // Guardar original
        Storage::putFileAs($basePath, $file, $originalName);

        // Obtener dimensiones originales
        $img = $this->manager->read($file->getRealPath());
        $originalWidth = $img->width();
        $originalHeight = $img->height();
        $mime = $file->getClientMimeType();

        $variantsData = [];

        foreach ($config['variants'] as $variantName => $variantConfig) {
            $ratio = $variantConfig['ratio'] ?? null;
            $sizes = $variantConfig['sizes'] ?? [];
            
            $variantsData[$variantName] = [];

            foreach ($sizes as $size) {
                // No escalar hacia arriba
                if ($size > $originalWidth) {
                    continue;
                }

                $height = null;
                if ($ratio) {
                    $height = (int) round($size * ($ratio[1] / $ratio[0]));
                }

                // Generar variante WebP
                $variantFilename = "{$filename}_{$variantName}_{$size}.webp";
                $variantPath = "{$basePath}/{$variantFilename}";
                
                $imgVariant = $this->manager->read($file->getRealPath());

                if ($ratio && $height) {
                    $imgVariant->cover($size, $height);
                } else {
                    $imgVariant->scale(width: $size);
                }

                $encoded = $imgVariant->toWebp(85);
                Storage::put($variantPath, $encoded);

                $variantsData[$variantName][$size] = Storage::url($variantPath);
            }
        }

        return [
            'profile' => $profile,
            'original' => Storage::url($originalPath),
            'variants' => $variantsData,
            'original_width' => $originalWidth,
            'original_height' => $originalHeight,
            'mime' => $mime,
        ];
    }
}
