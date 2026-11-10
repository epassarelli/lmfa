<?php

namespace App\Console\Commands;

use App\Models\{News, Event, PublicationTarget};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SocialPublicationBackfill extends Command
{
    protected $signature = 'social-publications:backfill {--apply : Modifica solo éxitos inequívocos; requiere autorización expresa}';
    protected $description = 'Propone identidades históricas; dry-run por defecto, bloquea ambigüedades';

    public function handle(): int
    {
        $page = substr(config('social_publication.destination'), strlen('facebook:page:'));
        $query = PublicationTarget::where('provider', 'facebook')->where('executor', 'laravel')
            ->whereHas('socialAccount', fn ($q) => $q->where('account_external_id', $page));
        $blocked = 0; $ready = 0;
        $query->chunkById(100, function ($targets) use (&$blocked, &$ready, $page) {
            foreach ($targets as $row) {
                DB::transaction(function () use ($row, &$blocked, &$ready, $page) {
                    $target = PublicationTarget::with(['request', 'attempts'])->lockForUpdate()->findOrFail($row->id);
                    $kind = [News::class => 'news', Event::class => 'events'][$target->request->content_type] ?? null;
                    $others = PublicationTarget::where('provider', 'facebook')->whereKeyNot($target->id)
                        ->whereHas('request', fn ($q) => $q->where('content_type', $target->request->content_type)->where('content_id', $target->request->content_id))
                        ->where(fn ($q) => $q->where('destination_key', config('social_publication.destination'))->orWhereHas('socialAccount', fn ($a) => $a->where('account_external_id', $page)))->exists();
                    $successes = $target->attempts->whereIn('status', ['success', 'published']);
                    $attempt = $successes->first();
                    if (!$kind || $others || !in_array($target->status, ['success', 'published']) || $successes->count() !== 1
                        || !preg_match('/^'.preg_quote($page, '/').'_[0-9]+$/D', $attempt->external_post_id ?? '')) {
                        $blocked++; $this->warn("Target {$target->id}: requiere conciliación manual."); return;
                    }
                    $ready++;
                    if (!$this->option('apply')) { $this->line("Target {$target->id}: éxito inequívoco {$kind} #{$target->request->content_id}."); return; }
                    $target->update(['ledger_content_kind' => $kind, 'ledger_content_id' => $target->request->content_id,
                        'destination_key' => config('social_publication.destination'), 'executor' => 'n8n', 'status' => 'success',
                        'external_post_id' => $attempt->external_post_id, 'external_url' => 'https://www.facebook.com/'.$attempt->external_post_id,
                        'published_at' => $attempt->finished_at ?? $attempt->created_at]);
                    \App\Models\AuditLog::create(['entity_type' => 'publication_target', 'entity_id' => $target->id,
                        'action' => 'legacy_backfill', 'new_values_json' => ['status' => 'success'], 'created_at' => now()]);
                });
            }
        });
        $this->info("Revisables: {$ready}; bloqueados: {$blocked}; modo: ".($this->option('apply') ? 'apply' : 'dry-run'));
        return $blocked ? self::FAILURE : self::SUCCESS;
    }
}
