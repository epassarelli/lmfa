<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Interprete;
use App\Services\Spotify\SpotifyAlbumImporter;
use App\Services\Spotify\SpotifyArtistLinker;
use App\Services\Spotify\SpotifyClient;
use App\Services\Spotify\SpotifyException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Vínculo con Spotify e importación manual de discos desde la ficha del artista.
 * Spec: project/docs/specs/spotify-artistas-discos.md
 */
class InterpreteSpotifyController extends Controller
{
    public function __construct(
        private SpotifyClient $client,
        private SpotifyArtistLinker $linker,
        private SpotifyAlbumImporter $importer,
    ) {
        $this->middleware('auth');
    }

    public function show(Interprete $interprete)
    {
        $this->authorize('update', $interprete);

        $configured = $this->client->isConfigured();
        $candidates = [];
        $preview = null;
        $error = null;

        if ($configured) {
            try {
                if (filled($interprete->spotify_artist_id)) {
                    $preview = $this->importer->preview($interprete);
                } else {
                    $candidates = $this->linker->candidates($interprete, true);
                }
            } catch (SpotifyException $e) {
                $error = $e->getMessage();
            }
        }

        return view('backend.interpretes.spotify', compact('interprete', 'configured', 'candidates', 'preview', 'error'));
    }

    public function link(Request $request, Interprete $interprete)
    {
        $this->authorize('update', $interprete);
        $data = $request->validate(['spotify' => ['required', 'string', 'max:255']]);

        try {
            $this->linker->link($interprete, $data['spotify']);
        } catch (SpotifyException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('backend.interpretes.spotify', $interprete)
            ->with('success', 'Artista vinculado a Spotify.');
    }

    public function unlink(Interprete $interprete)
    {
        $this->authorize('update', $interprete);
        $this->linker->unlink($interprete);

        return redirect()->route('backend.interpretes.spotify', $interprete)
            ->with('success', 'Se quitó el vínculo con Spotify. Los discos ya cargados no cambian.');
    }

    public function import(Request $request, Interprete $interprete)
    {
        $this->authorize('update', $interprete);
        $this->authorize('create', Album::class);

        $data = $request->validate([
            'albums' => ['required', 'array', 'min:1', 'max:60'],
            'albums.*' => ['string', 'regex:/^[A-Za-z0-9]{22}$/'],
        ], ['albums.required' => 'Marcá al menos un disco para importar.']);

        try {
            $result = $this->importer->import($interprete, $data['albums'], Auth::id());
        } catch (SpotifyException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = count($result['created']).' disco(s) creados como inactivos: los encontrás en Moderación.';
        if ($result['skipped'] > 0) {
            $message .= ' Se omitieron '.$result['skipped'].' que ya no estaban disponibles.';
        }

        return redirect()->route('backend.interpretes.spotify', $interprete)
            ->with('success', $message)
            ->with('import_errors', $result['errors']);
    }

    /** Vincula automáticamente (solo coincidencia exacta y única) a los artistas activos sin vínculo. */
    public function bulkLink(Request $request)
    {
        abort_unless(Auth::user()->hasRole('administrador'), 403);

        if (! $this->client->isConfigured()) {
            return back()->with('error', 'Spotify no está configurado en este entorno.');
        }

        $summary = ['linked' => 0, 'no_match' => 0, 'ambiguous' => 0, 'conflict' => 0];

        try {
            Interprete::query()
                ->where('estado', 1)
                ->whereNull('spotify_artist_id')
                ->orderByRaw('spotify_link_checked_at IS NOT NULL')
                ->orderBy('spotify_link_checked_at')
                ->orderBy('id')
                ->limit(40)
                ->get()
                ->each(function (Interprete $artist) use (&$summary) {
                    $status = $this->linker->autoLink($artist)['status'];
                    $summary[$status] = ($summary[$status] ?? 0) + 1;
                });
        } catch (SpotifyException $e) {
            return back()->with('error', $e->getMessage());
        }

        $pending = Interprete::where('estado', 1)->whereNull('spotify_artist_id')->count();

        return back()->with('success', "Vinculados: {$summary['linked']} · sin coincidencia exacta: {$summary['no_match']} · ambiguos: {$summary['ambiguous']} · en conflicto: {$summary['conflict']}. Quedan {$pending} activos sin vincular.");
    }
}
