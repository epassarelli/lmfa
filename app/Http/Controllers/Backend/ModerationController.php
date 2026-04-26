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
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador']);
    }

    private const PUBLISHABLE = [
        'news'       => News::class,
        'event'      => Event::class,
        'interprete' => Interprete::class,
        'album'      => Album::class,
        'cancion'    => Cancion::class,
    ];

    public function publish(string $type, int $id)
    {
        $modelClass = self::PUBLISHABLE[$type] ?? null;

        if (!$modelClass) {
            abort(404);
        }

        $model = $modelClass::findOrFail($id);

        if (in_array($type, ['news', 'event'])) {
            $model->update(['editorial_status' => 'published']);
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

        $interpretes = Interprete::where('estado', 0)->get();
        $albunes = Album::where('estado', 0)->get();
        $canciones = Cancion::where('estado', 0)->get();

        return view('backend.moderation.index', compact('news', 'events', 'interpretes', 'albunes', 'canciones'));
    }
}
