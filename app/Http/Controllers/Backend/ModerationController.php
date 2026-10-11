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
        } else {
            $model->update(['estado' => 1]);
        }

        return redirect()->route('backend.moderation.index')
            ->with('success', 'Contenido publicado correctamente.');
    }

    public function index()
    {
        $news = News::where(function ($q) {
                       $q->where('editorial_status', 'draft')
                         ->orWhereNull('published_at');
                   })
                   ->with('user')->get();

        $events = Event::where('editorial_status', 'draft')
                      ->with('user')->get();
        $programs = \App\Models\EventProgram::where('editorial_status', 'draft')->with('creator')->get();
        $pendingVenues = \App\Models\Venue::where('status', 'pending')->with('provincia')->get();

        $interpretes = Interprete::where('estado', 0)->get();
        $albunes = Album::where('estado', 0)->get();
        $canciones = Cancion::where('estado', 0)->get();

        return view('backend.moderation.index', compact('news', 'events', 'programs', 'pendingVenues', 'interpretes', 'albunes', 'canciones'));
    }
}
