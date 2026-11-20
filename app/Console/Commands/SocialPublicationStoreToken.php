<?php

namespace App\Console\Commands;

use App\Services\Publication\MetaCredentialStore;
use Illuminate\Console\Command;

class SocialPublicationStoreToken extends Command
{
    protected $signature = 'social-publications:store-token {provider : facebook|instagram}';
    protected $description = 'Guarda cifrado el token de Meta (se pide de forma oculta, nunca como argumento)';

    public function handle(MetaCredentialStore $store): int
    {
        $provider = $this->argument('provider');
        if (!in_array($provider, MetaCredentialStore::PROVIDERS, true)) { $this->error('Proveedor inválido.'); return self::INVALID; }
        $token = (string) $this->secret('Token de '.$provider);
        $store->put($provider, $token, null);
        $this->info('Token guardado cifrado. Verificalo con social-publications:check-token.');
        return self::SUCCESS;
    }
}
