<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShowRequest;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\PeniaProfile; // Mantengo el Request name por ahora para tocar lo mínimo de validación
use App\Models\Provincia;
use App\Services\EventService;
use App\Support\BackendListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use RealRashid\SweetAlert\Facades\Alert;

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

        $sortExpression = match ($sort) {
            'title' => 'COALESCE(event_programs.title, events.title)',
            'editorial_status' => 'COALESCE(event_programs.editorial_status, events.editorial_status)',
            'start_at' => 'COALESCE((SELECT MIN(day.start_at) FROM events day WHERE day.program_id = events.program_id), events.start_at)',
            default => 'events.created_at',
        };
        $events = Event::query()->select('events.*')->leftJoin('event_programs', 'event_programs.id', '=', 'events.program_id')
            ->where(function ($query) {
                $query->whereNull('events.program_id')->orWhereIn('events.id', Event::selectRaw('MIN(id)')->whereNotNull('program_id')->groupBy('program_id'));
            })
            ->when(! $user->hasRole('administrador'), function ($query) use ($user) {
                $query->where(fn ($own) => $own->where('events.created_by', $user->id)->orWhere(fn ($member) => $member->editableByMember($user)));
            })
            ->with([
                'user:id,name',
                'interpretes:id,interprete',
                'program' => fn ($query) => $query->select('id', 'title', 'editorial_status')->withCount('events')->withMin('events', 'start_at')->withMax('events', 'end_at'),
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($inner) use ($search) {
                    $inner->where('events.title', 'like', '%'.$search.'%')
                        ->orWhere('events.editorial_status', 'like', '%'.$search.'%')
                        ->orWhereHas('interpretes', fn ($relation) => $relation->where('interprete', 'like', '%'.$search.'%'))
                        ->orWhereHas('program', fn ($program) => $program->where('title', 'like', '%'.$search.'%')
                            ->orWhereHas('events.interpretes', fn ($artists) => $artists->where('interprete', 'like', '%'.$search.'%')));
                });
            })
            ->orderByRaw($sortExpression.' '.$direction)
            ->orderByDesc('events.id')
            ->paginate(25)
            ->withQueryString();

        return view('backend.events.index', compact('events'));
    }

    public function create(Request $request)
    {
        $event = new Event();
        if ($request->filled('program_id')) {
            $program = \App\Models\EventProgram::findOrFail($request->integer('program_id'));
            abort_unless(app(\App\Policies\EventPolicy::class)->updateProgram(auth()->user(), $program), 403);
            $event->fill($program->only(\App\Services\EventProgramService::COMMON_FIELDS));
            $event->program_id = $program->id;
            $event->title = $program->title.' — Jornada';
            if ($program->venue_id) {
                $event->fill(app(\App\Services\VenueService::class)->location($program->venue));
            }
        }
        $interpretes = Interprete::active()->get();
        $restrictedByFichas = auth()->user()->isRestrictedByEntities();
        $ownInterpreteIds = $restrictedByFichas ? auth()->user()->managedInterpreteIds() : [];
        $provincias = Provincia::all();
        $action = 'create';

        return view('backend.events.create', array_merge(compact('event', 'interpretes', 'provincias', 'action', 'restrictedByFichas', 'ownInterpreteIds'), $this->relationOptions()));
    }

    public function store(ShowRequest $request)
    {
        if ($request->input('event_format') === 'multiple') {
            $data = $request->validated();
            $data['editorial_status'] = ($data['estado'] ?? 0) == 1 ? 'published' : 'draft';
            $program = app(\App\Services\EventProgramService::class)->save($data, $request->file('imagen_destacada'));

            return redirect()->route('backend.event-programs.edit', $program)->with('success', 'Evento y jornadas creados correctamente.');
        }
        $event = $this->eventService->createEvent(
            $request->validated(),
            $request->file('imagen_destacada')
        );

        return redirect()->route('backend.events.index')->with('success', 'Evento creado correctamente.');
    }

    public function edit(Event $event)
    {
        $event->load(['interpretes', 'festivales:id', 'knowledgeArticles:id', 'peniaProfiles:id', 'venue', 'program']);
        $interpretes = Interprete::active()->get();
        $restrictedByFichas = auth()->user()->isRestrictedByEntities();
        $ownInterpreteIds = $restrictedByFichas ? auth()->user()->managedInterpreteIds() : [];
        $provincias = Provincia::all();
        $action = 'edit';

        return view('backend.events.edit', array_merge(compact('event', 'provincias', 'interpretes', 'action', 'restrictedByFichas', 'ownInterpreteIds'), $this->relationOptions()));
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

    public function addSessions(Event $event)
    {
        $this->authorize('update', $event);
        $program = app(\App\Services\EventProgramService::class)->fromEvent($event);

        return redirect()->route('backend.event-programs.edit', $program)->with('success', 'El evento ya tiene su primera jornada. Podés agregar las siguientes abajo.');
    }

    /**
     * Opciones de los selects de relaciones editoriales (Select2). Se exponen todos los
     * estados para poder vincular de antemano; la ficha pública filtra lo no publicado.
     */
    private function relationOptions(): array
    {
        return [
            'venues' => \App\Models\Venue::with('provincia')->where(fn ($q) => $q->where('status', 'active')
                ->when(auth()->user()->hasRole('administrador'), fn ($q) => $q->orWhere('status', 'pending')))->orderBy('name')->get(),
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
