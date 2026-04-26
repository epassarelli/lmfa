<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Interprete;
use App\Http\Requests\ShowRequest; // Mantengo el Request name por ahora para tocar lo mínimo de validación
use App\Models\Provincia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use App\Services\EventService;
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

    public function index()
    {
        $user = Auth::user();
        $events = Event::query()
            ->when($user->hasRole(['colaborador', 'prensa']), function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->with(['user', 'interpretes', 'images'])
            ->orderBy('start_at', 'desc')
            ->get();

        return view('backend.events.index', compact('events'));
    }

    public function create()
    {
        $event = new Event();
        $interpretes = Interprete::active()->get();
        $provincias = Provincia::all();
        $action = 'create';

        return view('backend.events.create', compact('event', 'interpretes', 'provincias', 'action'));
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
        $interpretes = Interprete::active()->get();
        $provincias = Provincia::all();
        $action = 'edit';

        return view('backend.events.edit', compact('event', 'provincias', 'interpretes', 'action'));
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
