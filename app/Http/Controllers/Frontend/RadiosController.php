<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Provincia;
use App\Models\RadioProgram;
use App\Models\RadioSignal;
use Illuminate\Http\Request;

class RadiosController extends Controller
{
    public function index(Request $request)
    {
        $signals = RadioSignal::publiclyVisible()->with(['provincia', 'listeningChannels' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->when($request->filled('province_id'), fn ($query) => $query->where('province_id', $request->integer('province_id')))
            ->when($request->filled('mode'), fn ($query) => $query->whereJsonContains('transmission_modes', $request->input('mode')))
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($nested) => $nested->where('title', 'like', '%'.$request->input('q').'%')->orWhere('city', 'like', '%'.$request->input('q').'%')))
            ->orderBy('title')->paginate(12)->withQueryString();

        return view('frontend.radios.index', ['signals' => $signals, 'programs' => RadioProgram::publiclyVisible()->whereNull('radio_signal_id')->with('slots')->orderBy('title')->limit(6)->get(), 'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']), 'metaTitle' => 'Radios y programas de folklore argentino', 'metaDescription' => 'Señales, frecuencias, streams y programas de folklore argentino verificados.', 'breadcrumbs' => [['label' => 'Radios', 'url' => route('radios.index')]]]);
    }

    public function show(string $slug)
    {
        $signal = RadioSignal::publiclyVisible()->where('slug', $slug)->with(['provincia', 'locality', 'listeningChannels' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'), 'programs' => fn ($query) => $query->publiclyVisible()->with('slots')->orderBy('title')])->firstOrFail();

        return view('frontend.radios.show', ['signal' => $signal, 'metaTitle' => $signal->seo_title ?: $signal->title.' | Radios de folklore', 'metaDescription' => $signal->meta_description ?: ($signal->excerpt ?: 'Información y formas de escucha de '.$signal->title.'.'), 'breadcrumbs' => [['label' => 'Radios', 'url' => route('radios.index')], ['label' => $signal->title]]]);
    }
}
