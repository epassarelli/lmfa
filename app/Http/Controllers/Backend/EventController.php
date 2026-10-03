<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\PeniaProfile;
use App\Http\Requests\ShowRequest; // Mantengo el Request name por ahora para tocar lo mínimo de validación
use App\Models\Provincia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use App\Services\EventService;
use App\Support\BackendListing;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->middleware('auth');
        $this->eventService = $eventService;
        $this->authorizeResource(Event::class, 'event');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        [$sort, $direction] = BackendListing::resolveSort(
            $request,
            ['start_at', 'title', 'editorial_status', 'created_at'],
            'start_at'
        );

        $events = Event::query()
            ->when($user->hasRole(['colaborador', 'prensa']), function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->with([
                'user:id,name',
                'interpretes:id,interprete',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('editorial_status', 'like', '%'.$search.'%')
                        ->orWhereHas('interpretes', fn ($relation) => $relation->where('interprete', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('backend.events.index', compact('events'));
    }

    public function create()
    {
        $event = new Event();
        $interpretes = Interprete::active()->get();
        $provincias = Provincia::all();
        $action = 'create';

        return view('backend.events.create', array_merge(compact('event', 'interpretes', 'provincias', 'action'), $this->relationOptions()));
    }

    public function store(ShowRequest $request)
    {
        $event = $this->eventService->createEvent(
            $request->validated(),
            $request->file('imagen_destacada')
        );

        return redirect()->route('backend.events.index')->with('success', 'Evento creado correctamente.');
    }

    public function edit(Event $event)
    {
        $event->load(['interpretes', 'festivales:id', 'knowledgeArticles:id', 'peniaProfiles:id']);
        $interpretes = Interprete::active()->get();
        $provincias = Provincia::all();
        $action = 'edit';

        return view('backend.events.edit', array_merge(compact('event', 'provincias', 'interpretes', 'action'), $this->relationOptions()));
    }

    public function update(ShowRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $this->eventService->updateEvent(
            $event,
            $request->validated(),
            $request->file('imagen_destacada')
        );

        return redirect()->route('backend.events.index')->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $event->delete();

        Alert::success('Evento eliminado', 'El evento ha sido eliminado con éxito.');
        return redirect()->route('backend.events.index');
    }

    /**
     * Opciones de los selects de relaciones editoriales (Select2). Se exponen todos los
     * estados para poder vincular de antemano; la ficha pública filtra lo no publicado.
     */
    private function relationOptions(): array
    {
        return [
            'festivalOptions' => Festival::query()->orderBy('title')->get(['id', 'title', 'status']),
            'knowledgeArticleOptions' => KnowledgeArticle::query()->orderBy('title')->get(['id', 'title', 'editorial_status as status']),
            'peniaOptions' => PeniaProfile::query()->orderBy('title')->get(['id', 'title', 'editorial_status as status']),
        ];
    }

    private function sendNotification(Event $event)
    {
        $details = [
            'title' => 'Se ha agregado un Evento en el portal',
            'event_title' => $event->title,
            'interprete' => $event->interpretes->first()->interprete ?? '—',
            'user' => $event->creator->name ?? '—',
        ];

        Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\ShowCreated($details));
    }
}
