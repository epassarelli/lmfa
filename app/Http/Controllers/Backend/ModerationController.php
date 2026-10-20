<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Event;
use App\Models\Interprete;
use App\Models\Album;
use App\Models\Cancion;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    protected $newsService;

    public function __construct(\App\Services\NewsService $newsService)
    {
        $this->middleware(['auth', 'role:administrador']);
        $this->newsService = $newsService;
    }

    private const PUBLISHABLE = [
        'news'       => News::class,
        'event'      => Event::class,
        'event-program' => \App\Models\EventProgram::class,
        'interprete' => Interprete::class,
        'album'      => \App\Models\Album::class,
        'cancion'    => \App\Models\Cancion::class,
        'festival'   => \App\Models\Festival::class,
    ];

    // Tarjeta del índice que queda expandida tras publicar cada tipo.
    private const CARD_KEYS = [
        'news' => 'news', 'event' => 'events', 'event-program' => 'programs',
        'interprete' => 'interpretes', 'album' => 'albumes', 'cancion' => 'canciones', 'festival' => 'festivales',
    ];

    public function publish(string $type, int $id)
    {
        $modelClass = self::PUBLISHABLE[$type] ?? null;

        if (!$modelClass) {
            abort(404);
        }

        $model = $modelClass::findOrFail($id);

        if ($type === 'news') {
            $this->newsService->updateNews($model, [
                'editorial_status' => 'published',
                'approved_by' => auth()->id(),
            ]);
        } elseif ($type === 'event') {
            app(\App\Services\EventService::class)->updateEvent($model, ['editorial_status' => 'published', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        } elseif ($type === 'event-program') {
            app(\App\Services\EventProgramService::class)->save(array_merge($model->only(['title', 'body', 'excerpt', 'venue_id', 'ticket_url', 'price_text', 'is_free']), ['editorial_status' => 'published']), null, $model);
        } elseif ($type === 'festival') {
            $model->update(['status' => 'published', 'published_at' => $model->published_at ?? now()]);
        } else {
            $model->update(['estado' => 1]);
        }

        app(\App\Services\AdminAttentionService::class)->forget();

        return redirect()->route('backend.moderation.index')
            ->with('success', 'Contenido publicado correctamente.')
            ->with('open', self::CARD_KEYS[$type] ?? null);
    }

    public function index()
    {
        // Las definiciones de "pendiente" viven en ModerationQueue (las comparte el tablero).
        $queue = \App\Support\ModerationQueue::class;
        $news = $queue::news()->with('user')->get();
        $events = $queue::events()->with('user')->get();
        $programs = $queue::programs()->with('creator')->get();
        $pendingVenues = $queue::venues()->with('provincia')->get();

        $interpretes = $queue::interpretes()->with('user')->get();
        $albunes = $queue::albums()->with('user')->get();
        $canciones = $queue::canciones()->with('user')->get();
        $festivales = $queue::festivales()->with(['user', 'provincia'])->get();

        $total = $news->count() + $events->count() + $programs->count() + $pendingVenues->count()
            + $interpretes->count() + $albunes->count() + $canciones->count() + $festivales->count();
        $open = session('open');

        return view('backend.moderation.index', compact('news', 'events', 'programs', 'pendingVenues', 'interpretes', 'albunes', 'canciones', 'festivales', 'total', 'open'));
    }
}
