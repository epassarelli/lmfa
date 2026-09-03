<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\RadioSignalRequest;
use App\Models\Provincia;
use App\Models\RadioSignal;
use App\Models\User;
use App\Services\RadioDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RadioSignalController extends Controller
{
    public function __construct(private readonly RadioDirectoryService $radios)
    {
        $this->middleware('auth');
        $this->authorizeResource(RadioSignal::class, 'radio_signal');
    }

    public function index(Request $request)
    {
        $signals = RadioSignal::query()->with(['provincia', 'verifier'])->withCount('programs')
            ->when(! Auth::user()->hasRole('administrador'), fn ($query) => $query->where('created_by', Auth::id()))
            ->when($request->filled('status'), fn ($query) => $query->where('editorial_status', $request->input('status')))
            ->when($request->filled('province_id'), fn ($query) => $query->where('province_id', $request->integer('province_id')))
            ->when($request->filled('mode'), fn ($query) => $query->whereJsonContains('transmission_modes', $request->input('mode')))
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->input('search').'%'))
            ->latest('updated_at')->paginate(25)->withQueryString();

        return view('backend.radios.signals.index', ['signals' => $signals, 'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre'])]);
    }

    public function create()
    {
        return view('backend.radios.signals.create', $this->formData(new RadioSignal()));
    }

    public function store(RadioSignalRequest $request)
    {
        $this->radios->saveSignal(new RadioSignal(), $request->validated() + ['created_by' => Auth::id()]);

        return redirect()->route('backend.radios.signals.index')->with('success', 'Señal creada.');
    }

    public function edit(RadioSignal $radioSignal)
    {
        return view('backend.radios.signals.edit', $this->formData($radioSignal->load('listeningChannels')));
    }

    public function update(RadioSignalRequest $request, RadioSignal $radioSignal)
    {
        $this->radios->saveSignal($radioSignal, $request->validated());

        return redirect()->route('backend.radios.signals.index')->with('success', 'Señal actualizada.');
    }

    public function destroy(RadioSignal $radioSignal)
    {
        $this->radios->saveSignal($radioSignal, ['editorial_status' => 'archived']);

        return back()->with('success', 'Señal archivada.');
    }

    public function publish(RadioSignal $radioSignal)
    {
        $this->authorize('update', $radioSignal);
        $this->radios->publishSignal($radioSignal);

        return back()->with('success', 'Señal publicada.');
    }

    private function formData(RadioSignal $signal): array
    {
        return compact('signal') + ['provincias' => Provincia::orderBy('nombre')->get(), 'users' => User::orderBy('name')->limit(200)->get(['id', 'name'])];
    }
}
