<?php

namespace App\Services\Publication;

use Illuminate\Support\Facades\Crypt;

/**
 * Tokens de Meta cifrados con la APP_KEY en storage/app/private (fuera de git y del webroot).
 * Nunca devuelve el token en listados ni lo escribe en logs. Instagram usa su token propio o, si no hay, el de Facebook.
 */
class MetaCredentialStore
{
    public const PROVIDERS = ['facebook', 'instagram'];

    public function put(string $provider, string $token, ?int $actor): void
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 422);
        $token = trim($token);
        abort_unless(preg_match('/^[A-Za-z0-9_\-|.]{40,1024}$/D', $token), 422, 'Token con formato inválido.');
        $data = $this->read();
        $data[$provider] = ['token' => Crypt::encryptString($token), 'saved_at' => now()->toIso8601String(), 'saved_by' => $actor,
            'last4' => substr($token, -4), 'checked_at' => null, 'check_ok' => null, 'check_name' => null, 'check_error' => null];
        $this->write($data);
    }

    public function forget(string $provider): void
    {
        $data = $this->read();
        unset($data[$provider]);
        $this->write($data);
    }

    public function token(string $provider): ?string
    {
        $data = $this->read();
        $entry = $data[$provider] ?? ($provider === 'instagram' ? ($data['facebook'] ?? null) : null);
        if (!$entry) { return null; }
        try { return Crypt::decryptString($entry['token']); }
        catch (\Illuminate\Contracts\Encryption\DecryptException) { return null; }
    }

    /** Metadatos sin secretos para el panel. */
    public function status(): array
    {
        $data = $this->read();
        $out = [];
        foreach (self::PROVIDERS as $provider) {
            $entry = $data[$provider] ?? null;
            $out[$provider] = $entry ? array_diff_key($entry, ['token' => true]) + ['configured' => true, 'inherited' => false]
                : ['configured' => $provider === 'instagram' && isset($data['facebook']), 'inherited' => $provider === 'instagram' && isset($data['facebook'])];
        }
        return $out;
    }

    public function recordCheck(string $provider, bool $ok, ?string $name, ?string $error): void
    {
        $data = $this->read();
        if (!isset($data[$provider])) { return; }
        $data[$provider] = array_replace($data[$provider], ['checked_at' => now()->toIso8601String(), 'check_ok' => $ok,
            'check_name' => $name ? mb_substr($name, 0, 120) : null, 'check_error' => $error ? mb_substr($error, 0, 200) : null]);
        $this->write($data);
    }

    private function read(): array
    {
        $path = config('social_publication.credentials_path');
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];
        return is_array($data) ? $data : [];
    }

    private function write(array $data): void
    {
        $path = config('social_publication.credentials_path');
        if (!is_dir(dirname($path))) { mkdir(dirname($path), 0750, true); }
        $tmp = $path.'.'.bin2hex(random_bytes(4)).'.tmp';
        file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod($tmp, 0600);
        rename($tmp, $path);
    }
}
