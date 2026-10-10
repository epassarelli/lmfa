<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\EventProgram;
use App\Support\CanonicalUrl;

class EventProgramController extends Controller
{
    public function show(string $slug)
    {
        $program = EventProgram::publiclyVisible()->with(['images', 'venue.provincia'])->where('slug', $slug)->firstOrFail();
        $sessions = $program->events()->publiclyVisible()
            ->with(['images', 'program.images', 'venue.provincia', 'provincia', 'interpretes' => fn ($q) => $q->where('estado', 1)->orderBy('event_interprete.sort_order')])
            ->orderBy('start_at')->orderBy('id')->paginate(12)->withQueryString();
        $canonicalUrl = CanonicalUrl::normalize(route('event-programs.show', $program->slug));
        $metaTitle = $program->title.' — Programación y jornadas';
        $metaDescription = $program->excerpt ?: \Illuminate\Support\Str::limit(trim(strip_tags($program->body)), 155);
        $metaRobots = $sessions->currentPage() > 1 ? 'noindex,follow' : 'index,follow';
        $breadcrumbs = [['label' => 'Cartelera', 'url' => route('cartelera.index')], ['label' => $program->title]];
        $programSchema = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => $program->title,
            'url' => $canonicalUrl, 'itemListElement' => $sessions->getCollection()->values()->map(fn ($event, $index) => [
                '@type' => 'ListItem', 'position' => ($sessions->currentPage() - 1) * $sessions->perPage() + $index + 1,
                'url' => CanonicalUrl::normalize(route('cartelera.show', $event->slug)), 'name' => $event->title,
            ])->all()];

        return view('frontend.shows.program', compact('program', 'sessions', 'canonicalUrl', 'metaTitle', 'metaDescription', 'metaRobots', 'breadcrumbs', 'programSchema'));
    }
}
