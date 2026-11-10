<?php

namespace App\Console\Commands;

use App\Models\PublicationTarget;
use App\Services\Publication\SocialPublicationLedgerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SocialPublicationMaintenance extends Command
{
    protected $signature = 'social-publications:reconcile';
    protected $description = 'Vence reservas y marca envíos inciertos sin llamar redes externas';

    public function handle(SocialPublicationLedgerService $ledger): int
    {
        $count = 0;
        PublicationTarget::where('executor', 'n8n')->whereIn('status', ['reserved', 'processing'])->where('lease_expires_at', '<', now())
            ->chunkById(100, function ($targets) use ($ledger, &$count) {
                foreach ($targets as $target) {
                    DB::transaction(function () use ($target, $ledger) { $ledger->expireLocked(PublicationTarget::lockForUpdate()->findOrFail($target->id)); });
                    $count++;
                }
            });
        $this->info("Reservas revisadas: {$count}. No se realizaron envíos.");
        return self::SUCCESS;
    }
}
