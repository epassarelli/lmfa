<?php

namespace App\Services\Publication;

use Illuminate\Support\Facades\Http;

/**
 * Llamadas a la API Graph de Meta usadas por el ejecutor Laravel, iguales a las del workflow n8n validado.
 * Nunca lanza excepciones de red: devuelve ['status' => 0, ...] para que el ejecutor clasifique el resultado.
 * El token viaja en la cabecera Authorization, nunca en la URL.
 */
class MetaGraphClient
{
    private function url(string $path): string
    {
        return 'https://graph.facebook.com/'.config('social_publication.graph_version').'/'.ltrim($path, '/');
    }

    private function call(callable $send): array
    {
        try {
            $response = $send();
            return ['status' => $response->status(), 'body' => $response->json() ?? []];
        } catch (\Throwable $e) {
            return ['status' => 0, 'body' => [], 'exception' => class_basename($e)];
        }
    }

    public function facebookPhoto(string $token, string $pageId, string $bytes, string $filename, string $caption): array
    {
        return $this->call(fn () => Http::withToken($token)->timeout(60)->attach('source', $bytes, $filename)
            ->post($this->url($pageId.'/photos'), ['caption' => $caption]));
    }

    public function instagramContainer(string $token, string $igUserId, string $imageUrl, string $caption): array
    {
        return $this->call(fn () => Http::withToken($token)->timeout(30)->asJson()
            ->post($this->url($igUserId.'/media'), ['image_url' => $imageUrl, 'caption' => $caption]));
    }

    public function instagramContainerStatus(string $token, string $containerId): array
    {
        return $this->call(fn () => Http::withToken($token)->timeout(30)->get($this->url($containerId), ['fields' => 'id,status_code']));
    }

    public function instagramPublish(string $token, string $igUserId, string $containerId): array
    {
        return $this->call(fn () => Http::withToken($token)->timeout(60)->asJson()
            ->post($this->url($igUserId.'/media_publish'), ['creation_id' => $containerId]));
    }

    public function instagramPermalink(string $token, string $mediaId): array
    {
        return $this->call(fn () => Http::withToken($token)->timeout(30)->get($this->url($mediaId), ['fields' => 'id,permalink,media_type']));
    }

    public function node(string $token, string $id, string $fields): array
    {
        return $this->call(fn () => Http::withToken($token)->timeout(20)->get($this->url($id), ['fields' => $fields]));
    }

    public function me(string $token): array
    {
        return $this->call(fn () => Http::withToken($token)->timeout(20)->get($this->url('me'), ['fields' => 'id,name']));
    }
}
