<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SocialPublicationAuditLegacy extends Command
{
    protected $signature = 'social-publications:audit-legacy';
    protected $description = 'Inventario de solo lectura de destinos legacy (sin credenciales)';

    public function handle(): int
    {
        $rows = DB::table('publication_targets as t')->join('publication_requests as r', 'r.id', '=', 't.publication_request_id')
            ->leftJoin('social_accounts as a', 'a.id', '=', 't.social_account_id')->where('t.executor', 'laravel')
            ->select(['r.content_type', 'r.content_id', 't.provider', 'a.account_external_id'])->selectRaw('COUNT(*) as targets')
            ->groupBy('r.content_type', 'r.content_id', 't.provider', 'a.account_external_id')->orderBy('r.content_id')->cursor();
        foreach ($rows as $row) { $this->line(json_encode($row, JSON_UNESCAPED_SLASHES)); }
        return self::SUCCESS;
    }
}
