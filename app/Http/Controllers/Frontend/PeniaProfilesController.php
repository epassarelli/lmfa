<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Support\CanonicalUrl;
use App\Support\SeoMetadata;
use Illuminate\Http\Request;

class PeniaProfilesController extends Controller
{
    public function index(Request $request)
    {
        $query = PeniaProfile::publiclyVisible()->with(['provincia', 'locality', 'images']);

        foreach (['province_id', 'locality_id', 'venue_type'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(fn ($nested) => $nested->where('title', 'like', "%{$term}%")->orWhere('city', 'like', "%{$term}%"));
        }

        $penias = $query->orderBy('title')->paginate(12)->withQueryString();
        $filtered = $request->filled('q') || $request->filled('province_id') || $request->filled('locality_id') || $request->filled('venue_type');

        return view('frontend.penia-profiles.index', [
            'penias' => $penias,
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
            'venueTypes' => $this->venueTypes(),
            'metaTitle' => 'Peñas folklóricas de Argentina',
            'metaDescription' => 'Encontrá peñas y espacios culturales de folklore con ubicación, contacto y verificación editorial vigente.',
            'canonical' => CanonicalUrl::normalize(route('penia-profiles.index')),
            'metaRobots' => $filtered ? 'noindex,follow' : 'index,follow',
            'breadcrumbs' => [['label' => 'Peñas']],
        ]);
    }

    public function show(string $slug)
    {
        $penia = PeniaProfile::publiclyVisible()
            ->with(['provincia', 'locality', 'images', 'events' => fn ($query) => $query->publiclyVisible()->where('start_at', '>=', now()->startOfDay())->with(['interpretes.images', 'images', 'provincia'])->orderBy('start_at')])
            ->where('slug', $slug)
            ->firstOrFail();

        $penia->increment('visits');
        $canonical = CanonicalUrl::normalize($penia->getUrl());
        $metaDescription = $penia->meta_description ?: ($penia->excerpt ?: SeoMetadata::clean($penia->body));
        $sameProvince = PeniaProfile::publiclyVisible()
            ->where('province_id', $penia->province_id)
            ->whereKeyNot($penia->id)
            ->with(['provincia', 'images'])
            ->orderBy('title')
            ->limit(3)
            ->get();

        return view('frontend.penia-profiles.show', compact('penia', 'canonical', 'metaDescription', 'sameProvince') + [
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
            'venueTypes' => $this->venueTypes(),
            'metaTitle' => $penia->seo_title ?: $penia->title,
            'metaRobots' => 'index,follow',
            'breadcrumbs' => [
                ['label' => 'Peñas', 'url' => route('penia-profiles.index')],
                ['label' => $penia->title],
            ],
        ]);
    }

    private function venueTypes(): array
    {
        return ['penia' => 'Peña', 'centro_cultural' => 'Centro cultural', 'gastronomico_cultural' => 'Espacio gastronómico-cultural', 'otro' => 'Otro'];
    }
}
