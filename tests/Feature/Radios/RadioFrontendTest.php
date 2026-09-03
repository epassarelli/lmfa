<?php

namespace Tests\Feature\Radios;

use App\Models\Provincia;
use App\Models\RadioListeningChannel;
use App\Models\RadioProgram;
use App\Models\RadioSignal;
use App\Models\User;
use App\Support\CanonicalUrl;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RadioFrontendTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['features.radio_directory' => true]);
        CarbonImmutable::setTestNow('2026-09-03 12:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_public_radio_and_program_pages_include_schedule_canonical_schema_and_sitemap(): void
    {
        $editor = User::factory()->create();
        $province = Provincia::query()->firstOrCreate(['nombre' => 'Córdoba']);
        $signal = RadioSignal::factory()->create([
            'title' => 'Radio Folklore Pública', 'slug' => 'radio-folklore-publica',
            'province_id' => $province->id, 'city' => 'Cosquín', 'address' => 'San Martín 100',
            'coverage_notes' => 'Valle de Punilla', 'seo_title' => 'Radio Folklore Pública en vivo',
            'meta_description' => 'Escuchá Radio Folklore Pública y consultá su programación folklórica.',
            'verification_status' => 'verified', 'last_verified_at' => now(), 'verified_by_user_id' => $editor->id,
            'verification_method' => 'official_source', 'editorial_status' => 'published', 'published_at' => now()->subDay(),
        ]);
        RadioListeningChannel::factory()->create(['radio_signal_id' => $signal->id, 'url' => 'https://radio.example.test/vivo']);
        $program = RadioProgram::factory()->create([
            'radio_signal_id' => $signal->id, 'title' => 'La ronda pública', 'slug' => 'la-ronda-publica',
            'seo_title' => 'La ronda pública | Programa de folklore',
            'meta_description' => 'Horarios y escucha oficial de La ronda pública.',
            'verification_status' => 'verified', 'last_verified_at' => now(), 'verified_by_user_id' => $editor->id,
            'verification_method' => 'official_source', 'editorial_status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $program->slots()->create(['weekday' => 4, 'starts_at' => '20:00', 'ends_at' => '22:00', 'timezone' => 'America/Argentina/Buenos_Aires', 'is_active' => true]);

        $this->get(route('radios.index'))->assertOk()->assertSee('Radio Folklore Pública')->assertSee(route('radios.programs.index'), false);
        $this->get($signal->getUrl())->assertOk()->assertSee('RadioStation')->assertSee('La ronda pública')->assertSee('Próxima emisión')->assertSee(CanonicalUrl::normalize($signal->getUrl()), false);
        $this->get(route('radios.programs.index'))->assertOk()->assertSee('La ronda pública')->assertSee('03/09 20:00');
        $this->get($program->getUrl())->assertOk()->assertSee('RadioSeries')->assertSee('03/09/2026 20:00')->assertSee('Radio Folklore Pública')->assertSee(CanonicalUrl::normalize($program->getUrl()), false);
        $this->get(route('sitemap.radios'))->assertOk()->assertSee(CanonicalUrl::normalize($signal->getUrl()), false)->assertSee(CanonicalUrl::normalize($program->getUrl()), false);

        $this->assertSame(1, $signal->fresh()->visits);
        $this->assertSame(1, $program->fresh()->visits);
    }

    public function test_filters_are_noindexed_and_programs_from_an_expired_signal_are_hidden(): void
    {
        $editor = User::factory()->create();
        $signal = RadioSignal::factory()->create([
            'title' => 'Radio vencida', 'slug' => 'radio-vencida',
            'verification_status' => 'verified', 'last_verified_at' => now()->subDays(91), 'verified_by_user_id' => $editor->id,
            'verification_method' => 'official_source', 'editorial_status' => 'published', 'published_at' => now()->subDay(),
        ]);
        RadioListeningChannel::factory()->create(['radio_signal_id' => $signal->id]);
        RadioProgram::factory()->create([
            'radio_signal_id' => $signal->id, 'title' => 'Programa no escuchable', 'slug' => 'programa-no-escuchable',
            'verification_status' => 'verified', 'last_verified_at' => now(), 'verified_by_user_id' => $editor->id,
            'verification_method' => 'official_source', 'editorial_status' => 'published', 'published_at' => now()->subDay(),
        ]);

        $this->get(route('radios.index', ['q' => 'vencida']))->assertOk()->assertSee('noindex,follow', false)->assertDontSee('Radio vencida');
        $this->get(route('radios.programs.index'))->assertOk()->assertDontSee('Programa no escuchable');
        $this->get('/radios-de-folklore-argentino/programas/programa-no-escuchable')->assertNotFound();
    }
}
