<?php

namespace Tests\Feature\Backend;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventCompactFormTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $user->assignRole('administrador');

        return $user;
    }

    private function assertCompactLayout(string $html): void
    {
        // Ancho completo (sin el contenedor angosto centrado) y estilo compacto.
        $this->assertStringNotContainsString('col-md-10 mx-auto', $html);
        $this->assertStringContainsString('class="form-compact"', $html);

        // Los campos conservan nombre e id y siguen el orden de la grilla de 12.
        $this->assertMatchesRegularExpression('/id="title".*id="start_at".*id="end_at".*id="published_at".*id="estado".*id="venue_id".*id="city".*id="address".*id="province_id".*id="interprete_ids".*id="festival_ids".*id="knowledge_article_ids".*id="penia_profile_ids".*id="ticket_url".*id="price_text".*id="is_free".*id="excerpt".*id="imagen_destacada".*id="editor"/s', $html);

        // Resumen e imagen destacada comparten fila (8 + 4) y ya no hay una tarjeta de imagen aparte.
        $this->assertStringContainsString('col-md-12 col-xl-8', $html);
        $this->assertStringNotContainsString('Imagen Destacada</h3>', $html);

        // Los bloques de "una sola fecha" siguen marcados para que el selector Una fecha / Varias jornadas los oculte.
        $this->assertGreaterThanOrEqual(7, substr_count($html, 'data-single-event'));

        // Ayudas como tooltip, no como texto fijo.
        $this->assertStringContainsString('data-toggle="tooltip"', $html);
        $this->assertStringNotContainsString('Dejar vacío para publicar ahora.</small>', $html);

        // Obligatorios según ShowRequest: título, inicio y detalle. El estado es opcional y siempre trae valor.
        $this->assertMatchesRegularExpression('/<input type="text" name="title"[^>]*\srequired/', $html);
        $this->assertMatchesRegularExpression('/<input type="datetime-local" name="start_at"[^>]*\srequired/', $html);
        $this->assertDoesNotMatchRegularExpression('/<select name="estado"[^>]*\srequired/', $html);

        // La ayuda general arranca contraída.
        $this->assertMatchesRegularExpression('/<details class="callout callout-info mb-3" data-form-help="event">/', $html);
    }

    public function test_the_create_form_uses_the_full_width_compact_layout(): void
    {
        $html = $this->actingAs($this->admin())->get(route('backend.events.create'))->assertOk()
            ->assertSee('Una fecha')->assertSee('Varias jornadas')->assertSee('Agregar jornada')
            ->getContent();

        $this->assertCompactLayout($html);
        $this->assertStringContainsString('data-image-hint="event"', $html);
    }

    public function test_the_edit_form_uses_the_full_width_compact_layout(): void
    {
        $admin = $this->admin();
        $event = Event::create([
            'title' => 'Evento compacto QA', 'slug' => 'e-'.uniqid(), 'body' => '<p>x</p>', 'start_at' => now()->addWeek(),
            'editorial_status' => 'draft', 'status' => 'active', 'modality' => 'presencial', 'created_by' => $admin->id,
        ]);

        $html = $this->actingAs($admin)->get(route('backend.events.edit', $event))->assertOk()->getContent();

        $this->assertCompactLayout($html);
        $this->assertStringContainsString('Evento compacto QA', $html);
    }
}
