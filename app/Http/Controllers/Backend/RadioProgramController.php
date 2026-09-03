<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\RadioProgramRequest;
use App\Models\RadioProgram;
use App\Models\RadioSignal;
use App\Models\User;
use App\Services\RadioDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RadioProgramController extends Controller
{
    public function __construct(private readonly RadioDirectoryService $radios)
    {
        $this->middleware('auth');
        $this->authorizeResource(RadioProgram::class, 'radio_program');
    }

    public function index(Request $request)
    {
        $programs = RadioProgram::query()->with(['signal', 'verifier'])->withCount('slots')
            ->when(! Auth::user()->hasRole('administrador'), fn ($query) => $query->where('created_by', Auth::id()))
            ->when($request->filled('status'), fn ($query) => $query->where('editorial_status', $request->input('status')))
            ->when($request->filled('verification'), fn ($query) => $query->where('verification_status', $request->input('verification')))
            ->when($request->filled('signal_id'), fn ($query) => $query->where('radio_signal_id', $request->integer('signal_id')))
            ->when($request->filled('platform'), fn ($query) => $query->where('platform', $request->input('platform')))
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->input('search').'%'))
            ->when($request->input('quality') === 'missing_slots', fn ($query) => $query->whereDoesntHave('slots', fn ($slot) => $slot->where('is_active', true)))
            ->when($request->input('quality') === 'missing_seo', fn ($query) => $query->where(fn ($missing) => $missing->whereNull('seo_title')->orWhereNull('meta_description')))
            ->latest('updated_at')->paginate(25)->withQueryString();

        return view('backend.radios.programs.index', ['programs' => $programs, 'signals' => RadioSignal::orderBy('title')->get(['id', 'title'])]);
    }

    public function create()
    {
        return view('backend.radios.programs.create', $this->formData(new RadioProgram()));
    }

    public function store(RadioProgramRequest $request)
    {
        $this->radios->saveProgram(new RadioProgram(), $request->validated() + ['created_by' => Auth::id()]);

        return redirect()->route('backend.radios.programs.index')->with('success', 'Programa creado.');
    }

    public function edit(RadioProgram $radioProgram)
    {
        return view('backend.radios.programs.edit', $this->formData($radioProgram->load('slots')));
    }

    public function update(RadioProgramRequest $request, RadioProgram $radioProgram)
    {
        $this->radios->saveProgram($radioProgram, $request->validated());

        return redirect()->route('backend.radios.programs.index')->with('success', 'Programa actualizado.');
    }

    public function destroy(RadioProgram $radioProgram)
    {
        $this->radios->saveProgram($radioProgram, ['editorial_status' => 'archived']);

        return back()->with('success', 'Programa archivado.');
    }

    public function publish(RadioProgram $radioProgram)
    {
        $this->authorize('publish', $radioProgram);
        $this->radios->publishProgram($radioProgram);

        return back()->with('success', 'Programa publicado.');
    }

    public function unpublish(RadioProgram $radioProgram)
    {
        $this->authorize('unpublish', $radioProgram);
        $this->radios->unpublishProgram($radioProgram);

        return back()->with('success', 'Programa despublicado y enviado a borrador.');
    }

    public function preview(RadioProgram $radioProgram)
    {
        $this->authorize('view', $radioProgram);
        $program = $radioProgram->load([
            'slots' => fn ($query) => $query->where('is_active', true)->orderBy('weekday')->orderBy('starts_at'),
            'signal.provincia',
            'signal.listeningChannels' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
        ]);

        return app(\App\Http\Controllers\Frontend\RadiosController::class)->programView($program, true);
    }

    private function formData(RadioProgram $program): array
    {
        $canManageEditorialState = Auth::user()->hasRole('administrador');

        return compact('program', 'canManageEditorialState') + ['signals' => RadioSignal::orderBy('title')->get(['id', 'title']), 'users' => $canManageEditorialState ? User::orderBy('name')->limit(200)->get(['id', 'name']) : collect()];
    }
}
