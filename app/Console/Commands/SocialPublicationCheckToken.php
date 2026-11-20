<?php

namespace App\Console\Commands;

use App\Services\Publication\SocialPublicationExecutor;
use Illuminate\Console\Command;

class SocialPublicationCheckToken extends Command
{
    protected $signature = 'social-publications:check-token {--no-alert : No enviar aviso por email}';
    protected $description = 'Verifica los tokens de Meta guardados (solo lectura, no publica)';

    public function handle(SocialPublicationExecutor $executor): int
    {
        $results = $executor->checkTokens(!$this->option('no-alert'));
        if (!$results) { $this->warn('No hay tokens cargados.'); return self::SUCCESS; }
        foreach ($results as $provider => $r) {
            $this->line($provider.': '.($r['ok'] ? 'OK ('.$r['name'].')' : 'ERROR: '.$r['error']));
        }
        return collect($results)->every(fn ($r) => $r['ok']) ? self::SUCCESS : self::FAILURE;
    }
}
