<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Provincia;
use App\Models\RadioProgram;
use App\Models\RadioSignal;
use App\Support\CanonicalUrl;
use App\Support\SeoMetadata;
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

        $filtered = $request->filled('q') || $request->filled('province_id') || $request->filled('mode');

        return view('frontend.radios.index', [
            'signals' => $signals,
            'programs' => RadioProgram::publiclyVisible()->whereNull('radio_signal_id')->with(['slots' => fn ($query) => $query->where('is_active', true)])->orderBy('title')->limit(6)->get(),
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
            'metaTitle' => 'Radios de folklore argentino: señales y streaming',
            'metaDescription' => 'Encontrá radios de folklore argentino, frecuencias y canales oficiales de streaming con información editorial verificada.',
            'canonical' => CanonicalUrl::normalize(route('radios.index')),
            'metaRobots' => $filtered ? 'noindex,follow' : 'index,follow',
            'breadcrumbs' => [['label' => 'Radios']],
        ]);
    }

    public function show(string $slug)
    {
        $signal = RadioSignal::publiclyVisible()->where('slug', $slug)->with(['provincia', 'locality', 'listeningChannels' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'), 'programs' => fn ($query) => $query->publiclyVisible()->with(['slots' => fn ($slots) => $slots->where('is_active', true)])->orderBy('title')])->firstOrFail();
        $signal->increment('visits');

        return $this->signalView($signal);
    }

    public function programs(Request $request)
    {
        $programs = RadioProgram::publiclyVisible()
            ->with(['signal' => fn ($query) => $query->publiclyVisible()->with('provincia'), 'slots' => fn ($query) => $query->where('is_active', true)])
            ->when($request->filled('signal_id'), fn ($query) => $query->where('radio_signal_id', $request->integer('signal_id')))
            ->when($request->filled('platform'), fn ($query) => $query->where('platform', $request->input('platform')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->input('q'));
                $query->where(fn ($nested) => $nested->where('title', 'like', "%{$term}%")
                    ->orWhereHas('signal', fn ($signal) => $signal->where('title', 'like', "%{$term}%")));
            })
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();
        $filtered = $request->filled('q') || $request->filled('signal_id') || $request->filled('platform');

        return view('frontend.radios.programs.index', [
            'programs' => $programs,
            'signals' => RadioSignal::publiclyVisible()->orderBy('title')->get(['id', 'title']),
            'metaTitle' => 'Programas de radio de folklore argentino',
            'metaDescription' => 'Descubrí programas radiales y streams dedicados al folklore argentino, sus horarios y formas oficiales de escucha.',
            'canonical' => CanonicalUrl::normalize(route('radios.programs.index')),
            'metaRobots' => $filtered ? 'noindex,follow' : 'index,follow',
            'breadcrumbs' => [['label' => 'Radios', 'url' => route('radios.index')], ['label' => 'Programas']],
        ]);
    }

    public function program(string $slug)
    {
        $program = RadioProgram::publiclyVisible()
            ->with([
                'slots' => fn ($query) => $query->where('is_active', true)->orderBy('weekday')->orderBy('starts_at'),
                'signal' => fn ($query) => $query->publiclyVisible()->with(['provincia', 'listeningChannels' => fn ($channels) => $channels->where('is_active', true)->orderBy('sort_order')]),
            ])
            ->where('slug', $slug)
            ->firstOrFail();
        $program->increment('visits');

        return $this->programView($program);
    }

    public function signalView(RadioSignal $signal, bool $preview = false)
    {
        $canonical = CanonicalUrl::normalize($signal->getUrl());

        return view('frontend.radios.show', [
            'signal' => $signal,
            'canonical' => $canonical,
            'metaTitle' => $signal->seo_title ?: $signal->title.' | Radios de folklore',
            'metaDescription' => $signal->meta_description ?: ($signal->excerpt ?: SeoMetadata::clean($signal->body)),
            'metaRobots' => $preview ? 'noindex,nofollow' : 'index,follow',
            'isPreview' => $preview,
            'breadcrumbs' => $preview
                ? [['label' => 'Administración'], ['label' => 'Vista previa'], ['label' => $signal->title]]
                : [['label' => 'Radios', 'url' => route('radios.index')], ['label' => $signal->title]],
        ]);
    }

    public function programView(RadioProgram $program, bool $preview = false)
    {
        $canonical = CanonicalUrl::normalize($program->getUrl());
        $relatedPrograms = RadioProgram::publiclyVisible()
            ->whereKeyNot($program->id)
            ->when($program->radio_signal_id, fn ($query) => $query->where('radio_signal_id', $program->radio_signal_id))
            ->with(['signal', 'slots' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('title')
            ->limit(3)
            ->get();

        return view('frontend.radios.programs.show', [
            'program' => $program,
            'relatedPrograms' => $relatedPrograms,
            'canonical' => $canonical,
            'metaTitle' => $program->seo_title ?: $program->title.' | Programa de folklore',
            'metaDescription' => $program->meta_description ?: ($program->excerpt ?: SeoMetadata::clean($program->body)),
            'metaRobots' => $preview ? 'noindex,nofollow' : 'index,follow',
            'isPreview' => $preview,
            'breadcrumbs' => $preview
                ? [['label' => 'Administración'], ['label' => 'Vista previa'], ['label' => $program->title]]
                : [['label' => 'Radios', 'url' => route('radios.index')], ['label' => 'Programas', 'url' => route('radios.programs.index')], ['label' => $program->title]],
        ]);
    }
}
