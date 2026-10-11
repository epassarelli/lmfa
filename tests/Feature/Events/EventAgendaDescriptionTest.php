<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventAgendaDescriptionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['responsecache.enabled' => false, 'responsecache.cache_store' => 'array']);
    }

    private function makeEvent(array $attributes): Event
    {
        $title = 'Evento descripcion '.uniqid();

        return Event::create(array_merge([
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => '<p>Contenido de prueba.</p>',
            'start_at' => now()->addDay(),
            'editorial_status' => 'published',
            'status' => 'active',
            'modality' => 'presencial',
        ], $attributes));
    }

    private function agendaDescription(Event $event): ?string
    {
        $html = $this->get(route('cartelera.index', ['q' => $event->title]))->assertOk()->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $blocks);

        foreach ($blocks[1] as $json) {
            foreach (json_decode($json, true)['@graph'] ?? [] as $node) {
                if (($node['name'] ?? null) === $event->title) {
                    return $node['description'] ?? null;
                }
            }
        }

        $this->fail('El evento no aparece en el JSON-LD de la cartelera.');
    }

    public function test_empty_html_body_falls_back_to_excerpt(): void
    {
        $event = $this->makeEvent(['body' => '<p></p>', 'excerpt' => 'Resumen real']);

        $this->assertSame('Resumen real', $this->agendaDescription($event));
    }

    public function test_meta_description_has_priority_like_the_detail_page(): void
    {
        $event = $this->makeEvent(['meta_description' => 'Descripcion SEO', 'excerpt' => 'Resumen', 'body' => '<p>Cuerpo</p>']);

        $this->assertSame('Descripcion SEO', $this->agendaDescription($event));
    }

    public function test_event_without_any_text_gets_generic_non_empty_description(): void
    {
        $event = $this->makeEvent(['body' => '<p> </p>']);

        $this->assertNotSame('', trim((string) $this->agendaDescription($event)));
    }
}
