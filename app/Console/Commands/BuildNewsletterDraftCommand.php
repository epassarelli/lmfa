<?php

namespace App\Console\Commands;

use App\Services\NewsletterEditionService;
use Illuminate\Console\Command;

/** Arma el borrador de la semana pasada. NUNCA envía nada: un administrador lo revisa y lo aprueba en el backoffice. */
class BuildNewsletterDraftCommand extends Command
{
    protected $signature = 'newsletter:build-draft';

    protected $description = 'Crea (si no existe) el borrador del newsletter de la semana pasada para su revisión.';

    public function handle(NewsletterEditionService $editions): int
    {
        [$start, $end] = NewsletterEditionService::lastWeek();

        $before = \App\Models\NewsletterEdition::where('edition_key', NewsletterEditionService::keyFor($start))->exists();
        $edition = $editions->buildDraft($start, $end);

        if (! $edition) {
            $this->info('No hay contenido nuevo la semana pasada: no se creó borrador.');

            return self::SUCCESS;
        }

        $this->info($before
            ? "Ya existía la edición {$edition->edition_key} ({$edition->statusLabel()})."
            : "Borrador creado: {$edition->edition_key}. Revisalo y aprobalo en el backoffice (Newsletter → Ediciones).");

        return self::SUCCESS;
    }
}
