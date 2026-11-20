<?php

namespace App\Console\Commands;

use App\Services\Publication\{SocialPublicationExecutor, SocialPublicationSettings};
use Illuminate\Console\Command;

class SocialPublicationRun extends Command
{
    protected $signature = 'social-publications:run
        {--due : Ejecutar solo los horarios vencidos configurados en el panel (lo usa el programador)}
        {--kind= : news|events (envío puntual)}
        {--provider= : facebook|instagram (envío puntual)}
        {--id= : ID del contenido; si se omite, el primero de la cola}';
    protected $description = 'Publica en Facebook/Instagram desde Laravel (requiere ejecutor laravel en el panel)';

    public function handle(SocialPublicationExecutor $executor): int
    {
        if ($this->option('due')) {
            foreach ($executor->runDue() as $result) { $this->line($this->format($result)); }
            return self::SUCCESS;
        }
        $kind = $this->option('kind'); $provider = $this->option('provider');
        if (!isset(SocialPublicationSettings::KINDS[$kind], SocialPublicationSettings::PROVIDERS[$provider])) {
            $this->error('Indicá --due, o --kind=news|events y --provider=facebook|instagram.');
            return self::INVALID;
        }
        $result = $executor->publish($kind, $provider, $this->option('id') ? (int) $this->option('id') : null, null, 'console');
        $this->line($this->format($result));
        return in_array($result['status'], ['success', 'empty'], true) ? self::SUCCESS : self::FAILURE;
    }

    private function format(array $r): string
    {
        return sprintf('[%s/%s] %s #%s: %s %s', $r['kind'], $r['provider'], $r['status'], $r['content_id'] ?? '-', $r['message'], $r['external_url'] ?? '');
    }
}
