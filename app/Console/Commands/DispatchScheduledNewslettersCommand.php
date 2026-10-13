<?php

namespace App\Console\Commands;

use App\Services\NewsletterEditionService;
use Illuminate\Console\Command;

/** Envía únicamente ediciones APROBADAS cuya hora programada ya llegó. Un borrador nunca se envía. */
class DispatchScheduledNewslettersCommand extends Command
{
    protected $signature = 'newsletter:dispatch-scheduled';

    protected $description = 'Despacha las ediciones aprobadas del newsletter cuya hora programada ya llegó.';

    public function handle(NewsletterEditionService $editions): int
    {
        $count = $editions->dispatchScheduled();

        $this->info($count ? "Se despacharon {$count} edición(es)." : 'No hay ediciones programadas para enviar.');

        return self::SUCCESS;
    }
}
