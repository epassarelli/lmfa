<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SocialPublicationIssueToken extends Command
{
    protected $signature = 'social-publications:issue-token {user} {--issue : Emite el secreto una sola vez} {--n8n-file : Guarda la credencial en archivo privado en lugar de mostrarla}';
    protected $description = 'Valida el operador y opcionalmente emite un PAT acotado sin otorgar permisos';

    public function handle(): int
    {
        $user = User::findOrFail($this->argument('user'));
        $abilities = ['social-publications:read', 'social-publications:execute'];
        if ($user->hasRole('administrador') || !in_array((string) $user->id, array_map('strval', config('social_publication.operators')), true)
            || !$user->checkPermissionTo($abilities[0]) || !$user->checkPermissionTo($abilities[1])) {
            $this->error('Se requiere operador no administrador, configurado y con ambos permisos explícitos.');
            return self::FAILURE;
        }
        if (!$this->option('issue')) { $this->info('Operador válido. No se emitió token.'); return self::SUCCESS; }
        if (!$this->option('n8n-file')) {
            $this->line($user->createToken('n8n-social-publications', $abilities)->plainTextToken);
            return self::SUCCESS;
        }
        $directory = storage_path('app/private');
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            $this->error('No se pudo crear el directorio privado.'); return self::FAILURE;
        }
        $file = $directory.'/social-publications-n8n.json';
        $previousMask = umask(0077);
        try { $handle = @fopen($file, 'x'); } finally { umask($previousMask); }
        if (!$handle) { $this->error('El archivo privado ya existe o no es escribible. No se emitió token.'); return self::FAILURE; }
        $token = null;
        try {
            $token = $user->createToken('n8n-social-publications', $abilities);
            $credential = [['id' => 'MfaSocialPortal1', 'name' => 'MFA - Publicaciones Portal', 'type' => 'httpHeaderAuth',
                'data' => ['name' => 'Authorization', 'value' => 'Bearer '.$token->plainTextToken]]];
            $encoded = json_encode($credential, JSON_THROW_ON_ERROR);
            if (fwrite($handle, $encoded) !== strlen($encoded) || !fflush($handle)) { throw new \RuntimeException('No se pudo conservar la credencial completa.'); }
            fclose($handle);
            $this->info('Credencial preparada sin mostrar el token: storage/app/private/social-publications-n8n.json');
            $this->warn('Transferir en privado a n8n; no subir el archivo al repositorio ni al chat.');
        } catch (\Throwable $exception) {
            if (is_resource($handle)) { fclose($handle); }
            $token?->accessToken->delete();
            if (is_file($file)) { unlink($file); }
            $this->error('No se completó la exportación privada; el token de este intento se revocó.');
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}
