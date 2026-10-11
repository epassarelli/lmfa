<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventProgram;
use App\Models\Interprete;
use App\Models\Provincia;
use App\Models\Venue;
use App\Services\EventProgramService;
use Illuminate\Http\Request;

class EventProgramController extends Controller
{
    public function __construct(private EventProgramService $programs)
    {
        $this->middleware('auth');
    }

    private function authorizeProgram(?EventProgram $program = null): void
    {
        $this->authorize('create', Event::class);
        abort_unless(! $program || auth()->user()->hasRole('administrador') || (int) $program->created_by === (int) auth()->id(), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeProgram();

        return redirect()->route('backend.events.index', $request->only('search'));
    }

    public function create()
    {
        $this->authorizeProgram();

        return redirect()->route('backend.events.create', ['format' => 'multiple']);
    }

    public function store(Request $request)
    {
        $this->authorizeProgram();
        $request->validate(['imagen_destacada' => 'nullable|image|max:5120']);
        $program = $this->programs->save($request->all(), $request->file('imagen_destacada'));

        return redirect()->route('backend.event-programs.edit', $program)->with('success', 'Evento y jornadas creados.');
    }

    public function edit(EventProgram $event_program)
    {
        $this->authorizeProgram($event_program);

        return $this->form($event_program);
    }

    public function update(Request $request, EventProgram $event_program)
    {
        $this->authorizeProgram($event_program);
        $request->validate(['imagen_destacada' => 'nullable|image|max:5120']);
        $this->programs->save($request->all(), $request->file('imagen_destacada'), $event_program);

        return back()->with('success', 'Datos comunes actualizados. Se conservaron las excepciones y el estado de cada jornada.');
    }

    private function form(EventProgram $program)
    {
        $program->load(['venue', 'images']);
        $sessions = $program->exists ? $program->events()->with('interpretes')->orderBy('start_at')->orderBy('id')->paginate(20) : null;
        $venues = Venue::with('provincia')->where(fn ($q) => $q->where('status', 'active')->orWhere('id', $program->venue_id))
            ->when(auth()->user()->hasRole('administrador'), fn ($q) => $q->orWhere('status', 'pending'))->orderBy('name')->get();

        return view('backend.event-programs.form', ['program' => $program, 'sessions' => $sessions,
            'venues' => $venues, 'provincias' => Provincia::orderBy('nombre')->get(), 'interpretes' => Interprete::active()->orderBy('interprete')->get()]);
    }
}
