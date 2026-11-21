<?php

namespace App\Services\Publication;

use App\Models\{Album, MediaAsset, SocialPublicationEligibility};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * Cola automática de Discos (spec social-autopublicacion-discos): discos activos de intérpretes activos, con slug,
 * nombre y portada cargada, con ID mayor al umbral y sin envío previo a ese destino, por ID ascendente (igual que Noticias).
 * Instagram exige éxito previo en Facebook.
 */
class AutomaticAlbumPublication
{
    private function destination(string $provider): string
    {
        return config($provider === 'instagram' ? 'social_publication.instagram_destination' : 'social_publication.destination');
    }

    public function activeQuery(string $provider = 'facebook'): Builder
    {
        return Album::query()->select('albunes.*')
            ->where('albunes.estado', 1)
            ->where('albunes.id', '>', (int) config('social_publication.albums_after_id'))
            ->whereNotNull('albunes.slug')->where('albunes.slug', '<>', '')
            ->whereNotNull('albunes.album')->where('albunes.album', '<>', '')
            ->whereHas('interprete', fn ($q) => $q->where('estado', 1)->whereNotNull('slug')->where('slug', '<>', ''))
            ->where(function ($cover) {
                $cover->where(fn ($legacy) => $legacy->whereNotNull('albunes.foto')->where('albunes.foto', '<>', ''))
                    ->orWhereExists(fn ($m) => $m->selectRaw('1')->from('media_assets as ma')
                        ->where('ma.imageable_type', Album::class)->whereColumn('ma.imageable_id', 'albunes.id')
                        ->whereNotNull('ma.original_path')->where('ma.original_path', '<>', ''));
            });
    }

    public function candidates(string $provider = 'facebook'): Builder
    {
        $destination = $this->destination($provider);
        $page = substr($destination, strrpos($destination, ':') + 1);
        return $this->activeQuery($provider)->when($provider === 'instagram', fn ($q) => $q->whereExists(function ($f) {
                $f->selectRaw('1')->from('publication_targets as fb')->where('fb.provider', 'facebook')
                    ->where('fb.destination_key', config('social_publication.destination'))->where('fb.ledger_content_kind', 'albums')
                    ->whereColumn('fb.ledger_content_id', 'albunes.id')->whereIn('fb.status', ['success', 'published']);
            }))
            ->whereNotExists(function ($q) use ($destination, $provider) {
                // Igual que Noticias y Eventos: solo vuelve a la cola lo que quedó en un estado reintentable y seguro.
                $q->selectRaw('1')->from('publication_targets as t')
                    ->where('t.ledger_content_kind', 'albums')->whereColumn('t.ledger_content_id', 'albunes.id')
                    ->where('t.provider', $provider)->where('t.destination_key', $destination)
                    ->whereNot(function ($safe) {
                        $safe->where('t.attempts_count', '<', 3)
                            ->where(fn ($retry) => $retry->whereNull('t.retry_after')->orWhere('t.retry_after', '<=', now()))
                            ->where(function ($state) {
                                $state->whereIn('t.status', ['pending', 'failed', 'cancelled'])
                                    ->orWhere(fn ($reserved) => $reserved->where('t.status', 'reserved')
                                        ->whereNotNull('t.lease_expires_at')->where('t.lease_expires_at', '<=', now()));
                            });
                    });
            })->whereNotExists(function ($q) use ($destination, $page, $provider) {
                $q->selectRaw('1')->from('publication_targets as lt')
                    ->join('publication_requests as lr', 'lr.id', '=', 'lt.publication_request_id')
                    ->leftJoin('social_accounts as la', 'la.id', '=', 'lt.social_account_id')
                    ->where('lr.content_type', Album::class)->whereColumn('lr.content_id', 'albunes.id')
                    ->where('lt.provider', $provider)->where('lt.executor', 'laravel')
                    ->where(fn ($d) => $d->where('lt.destination_key', $destination)->orWhere('la.account_external_id', $page));
            })->orderBy('albunes.id');
    }

    public function facebookPublished(int $id): bool
    {
        return \App\Models\PublicationTarget::where('provider', 'facebook')->where('destination_key', config('social_publication.destination'))
            ->where('ledger_content_kind', 'albums')->where('ledger_content_id', $id)->whereIn('status', ['success', 'published'])->exists();
    }

    public function eligibility(Album $album, string $provider = 'facebook'): SocialPublicationEligibility
    {
        return new SocialPublicationEligibility(['content_kind' => 'albums', 'content_id' => $album->id, 'provider' => $provider,
            'destination_key' => $this->destination($provider), 'enabled' => true, 'portal_only_override' => true]);
    }

    /**
     * Portada del disco en el disco público (ruta relativa) o null si no hay archivo legible.
     * Prioriza la imagen gestionada (media_assets, original acotado) y después la `foto` legacy de storage/albunes.
     */
    public function coverPath(Album $album): ?string
    {
        $disk = Storage::disk('public');
        $media = MediaAsset::where('imageable_type', Album::class)->where('imageable_id', $album->id)
            ->whereNotNull('original_path')->orderBy('sort_order')->orderBy('id')->get(['id', 'disk', 'original_path']);
        foreach ($media as $asset) {
            $path = ltrim((string) preg_replace('#^/?storage/#', '', $asset->original_path), '/');
            if (($asset->disk ?: 'public') === 'public' && $path !== '' && !filter_var($path, FILTER_VALIDATE_URL) && $disk->exists($path)) {
                return $path;
            }
        }
        if (filled($album->foto)) {
            $path = 'albunes/'.ltrim($album->foto, '/');
            if ($disk->exists($path)) { return $path; }
        }
        return null;
    }
}
