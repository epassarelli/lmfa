<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;

class ImagenController extends Controller
{



  function generarMiniaturas($carpeta, $ancho, $alto, $calidad = 50)
  {
    // Obtener las imágenes de la carpeta
    $imagenes = Storage::disk('local')->files("public/{$carpeta}");
    // dd($imagenes);
    // Recorrer las imágenes y generar las miniaturas
    foreach ($imagenes as $imagen) {
      // Obtener el nombre del archivo sin la ruta
      $nombreArchivo = pathinfo($imagen, PATHINFO_FILENAME);
      // Obtener la extensión del archivo
      $extension = pathinfo($imagen, PATHINFO_EXTENSION);
      // dd($nombreArchivo, $extension);
      // dd(storage_path("app/public/{$imagen}"));
      // Obtener la imagen original
      $imagenOriginal = Image::make(storage_path($imagen));


      // Redimensionar la imagen manteniendo la proporción y recortando en el centro si es necesario
      $imagenMiniatura = Image::make($imagenOriginal)
        ->fit($ancho, $alto, function ($constraint) {
          $constraint->upsize();
          $constraint->aspectRatio();
        }, 'center');

      // Guardar la miniatura en la carpeta "thumb"
      Storage::disk('local')->put("public/{$carpeta}/thumb/{$nombreArchivo}.{$extension}", $imagenMiniatura->encode($extension, $calidad));
    }
  }
}
