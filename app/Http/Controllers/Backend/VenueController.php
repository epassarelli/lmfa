<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Provincia;
use App\Models\Venue;
use App\Services\VenueService;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function __construct(private VenueService $venues)
    {
        $this->middleware(['auth', 'role:administrador']);
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $venues = Venue::with('provincia')->withCount('events')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('city', 'like', '%'.$search.'%')->orWhere('address', 'like', '%'.$search.'%')))
            ->when(in_array($request->input('status'), ['active', 'inactive', 'pending']), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('name')->orderBy('id')->paginate(25)->withQueryString();

        return view('backend.venues.index', compact('venues'));
    }

    public function create()
    {
        return view('backend.venues.form', ['venue' => new Venue, 'provincias' => Provincia::orderBy('nombre')->get()]);
    }

    public function store(Request $request)
    {
        $venue = $this->venues->save($request->all());

        return redirect()->route('backend.venues.edit', $venue)->with('success', 'Lugar creado. Ya podés seleccionarlo en Eventos.');
    }

    public function edit(Venue $venue)
    {
        return view('backend.venues.form', ['venue' => $venue, 'provincias' => Provincia::orderBy('nombre')->get()]);
    }

    public function update(Request $request, Venue $venue)
    {
        $this->venues->save($request->all(), $venue);

        return back()->with('success', 'Lugar actualizado, incluida la ubicación de sus eventos vinculados.');
    }

    public function destroy(Venue $venue)
    {
        if (! $this->venues->delete($venue)) {
            return back()->with('error', 'Este lugar tiene eventos o conjuntos vinculados. Podés inactivarlo desde Editar.');
        }

        return back()->with('success', 'Lugar eliminado.');
    }
}
