<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class TestImageController extends Controller
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Ejemplo de cómo subir una imagen para una noticia.
     */
    public function store(Request $request, Noticia $noticia)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $result = $this->imageService->process(
                $request->file('foto'),
                'news_full', // Perfil definido en config/image_profiles.php
                'noticias',  // Carpeta de destino
                $noticia->slug // Nombre base opcional
            );

            // Guardar en la tabla polimórfica
            $noticia->images()->create([
                'profile' => $result['profile'],
                'original_path' => $result['original'],
                'variants_json' => $result['variants'],
                'original_width' => $result['original_width'],
                'original_height' => $result['original_height'],
                'mime' => $result['mime'],
                'alt' => $noticia->titulo,
            ]);

            return response()->json([
                'message' => 'Imagen procesada y guardada correctamente.',
                'data' => $result
            ]);
        }

        return response()->json(['message' => 'No se subió ninguna foto.'], 400);
    }
}
