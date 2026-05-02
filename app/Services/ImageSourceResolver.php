<?php

namespace App\Services;

use App\Exceptions\ImageDownloadException;
use App\Exceptions\ImageInvalidException;
use App\Exceptions\ImageSecurityException;
use App\Exceptions\ImageSourceException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageSourceResolver
{
    protected string $tmpDir = 'tmp/news-images';

    /**
     * Resuelve una fuente de imagen (Archivo, Path o URL) a un objeto UploadedFile.
     */
    public function resolve($source): ?UploadedFile
    {
        if (!$source) return null;

        if ($source instanceof UploadedFile) {
            return $source;
        }

        if (is_string($source)) {
            if (filter_var($source, FILTER_VALIDATE_URL)) {
                return $this->resolveFromUrl($source);
            }

            if (file_exists($source) && is_readable($source)) {
                return $this->resolveFromPath($source);
            }
        }

        throw new ImageInvalidException("Fuente de imagen no reconocida o no válida.");
    }

    /**
     * Descarga de forma segura una imagen desde una URL externa.
     */
    protected function resolveFromUrl(string $url): UploadedFile
    {
        $this->validateUrlSecurity($url);

        try {
            // Pre-flight con HEAD para evitar descargar archivos gigantes sin necesidad
            $headResponse = Http::timeout(3)->head($url);
            
            if ($headResponse->successful()) {
                $contentLength = (int) $headResponse->header('Content-Length');
                if ($contentLength > 5 * 1024 * 1024) {
                    throw new ImageInvalidException("La imagen excede el límite de 5MB (vía HEAD).");
                }
            }

            $response = Http::timeout(5)->get($url);

            if (!$response->successful()) {
                throw new ImageDownloadException("No se pudo descargar la imagen. Estado: " . $response->status());
            }

            $contentType = $response->header('Content-Type');
            if (!str_starts_with($contentType, 'image/')) {
                throw new ImageInvalidException("La URL no apunta a una imagen válida (Content-Type: $contentType).");
            }

            $content = $response->body();
            if (strlen($content) > 5 * 1024 * 1024) {
                throw new ImageInvalidException("La imagen excede el límite de 5MB.");
            }

            $extension = $this->getExtensionFromMime($contentType);
            $filename = Str::random(40) . '.' . $extension;
            $tmpPath = $this->tmpDir . '/' . $filename;

            Storage::disk('local')->put($tmpPath, $content);
            $fullPath = Storage::disk('local')->path($tmpPath);

            // Marcamos como test: false para que no intente validar el path con is_uploaded_file (ya que es un tmp local)
            return new UploadedFile($fullPath, $filename, $contentType, null, true);

        } catch (\Exception $e) {
            if ($e instanceof ImageSourceException) throw $e;
            throw new ImageDownloadException("Error al descargar la imagen: " . $e->getMessage());
        }
    }

    /**
     * Crea un UploadedFile desde un path local existente.
     */
    protected function resolveFromPath(string $path): UploadedFile
    {
        $mime = mime_content_type($path);
        if (!str_starts_with($mime, 'image/')) {
            throw new ImageInvalidException("El archivo local no es una imagen válida.");
        }

        $filename = basename($path);
        return new UploadedFile($path, $filename, $mime, null, true);
    }

    /**
     * Valida la URL para prevenir ataques SSRF.
     */
    protected function validateUrlSecurity(string $url): void
    {
        $parsed = parse_url($url);
        
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            throw new ImageSecurityException("Protocolo no permitido: " . ($parsed['scheme'] ?? 'none'));
        }

        $host = $parsed['host'] ?? '';
        
        // Evitar acceso a localhost o IPs privadas
        $ip = gethostbyname($host);

        if ($this->isPrivateIp($ip)) {
            throw new ImageSecurityException("Acceso bloqueado a dirección IP privada o local: $ip");
        }
    }

    /**
     * Verifica si una IP pertenece a rangos privados o reservados.
     */
    protected function isPrivateIp(string $ip): bool
    {
        // filter_var con estos flags devuelve false si la IP es privada o reservada
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    protected function getExtensionFromMime(string $mime): string
    {
        $mimes = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        return $mimes[$mime] ?? 'img';
    }
}
