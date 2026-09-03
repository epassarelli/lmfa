<?php

namespace Tests\Feature\Penias;

use App\Models\Event;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use Database\Seeders\PeniaProfilePilotSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PeniaProfilePilotSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_replaces_known_demo_content_with_ten_safe_editorial_drafts(): void
    {
        Role::findOrCreate('administrador', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $province = Provincia::query()->firstOrCreate(['nombre' => 'Salta']);

        $demo = PeniaProfile::factory()->create([
            'slug' => 'penia-demo-1',
            'province_id' => $province->id,
            'verification_status' => 'verified',
            'last_verified_at' => now(),
            'verified_by_user_id' => $admin->id,
            'verification_method' => 'official_source',
            'editorial_status' => 'published',
            'published_at' => now(),
        ]);
        $event = Event::query()->create([
            'title' => 'Evento demo de Peña',
            'slug' => 'evento-demo-penia-1',
            'body' => '<p>Contenido ficticio.</p>',
            'start_at' => now()->addWeek(),
            'province_id' => $province->id,
            'status' => 'active',
            'editorial_status' => 'published',
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);
        $demo->events()->attach($event);

        $this->assertSame(0, Artisan::call('db:seed', [
            '--class' => PeniaProfilePilotSeeder::class,
            '--force' => true,
        ]));

        $profiles = PeniaProfile::query()
            ->whereIn('slug', PeniaProfilePilotSeeder::PILOT_SLUGS)
            ->get();

        $this->assertCount(10, $profiles);
        $profiles->each(function (PeniaProfile $profile) use ($admin): void {
            $sources = implode(' ', $profile->source_urls);

            $this->assertSame('draft', $profile->editorial_status);
            $this->assertSame('pending', $profile->verification_status);
            $this->assertNull($profile->published_at);
            $this->assertNull($profile->last_verified_at);
            $this->assertNull($profile->verified_by_user_id);
            $this->assertNull($profile->verification_method);
            $this->assertSame($admin->id, $profile->created_by);
            $this->assertCount(2, $profile->source_urls);
            $this->assertStringContainsString('saltaciudad.travel', $sources);
            $this->assertStringNotContainsString('example.test', $sources);
            $this->assertGreaterThanOrEqual(150, str_word_count(strip_tags($profile->body)));
            $this->assertNotEmpty($profile->seo_title);
            $this->assertNotEmpty($profile->meta_description);
        });

        $demo->refresh();
        $this->assertSame('archived', $demo->editorial_status);
        $this->assertSame('outdated', $demo->verification_status);
        $this->assertNull($demo->published_at);
        $this->assertNull($demo->verified_by_user_id);
        $this->assertCount(0, $demo->events);
        $this->assertSame('archived', $event->fresh()->editorial_status);
    }

    public function test_rerunning_the_pilot_does_not_overwrite_an_existing_reviewed_profile(): void
    {
        Role::findOrCreate('administrador', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        Artisan::call('db:seed', [
            '--class' => PeniaProfilePilotSeeder::class,
            '--force' => true,
        ]);

        $reviewed = PeniaProfile::query()->where('slug', PeniaProfilePilotSeeder::PILOT_SLUGS[0])->firstOrFail();
        $reviewed->forceFill([
            'title' => 'La Cautiva revisada por un editor',
            'verification_status' => 'verified',
            'last_verified_at' => now(),
            'verified_by_user_id' => $admin->id,
            'verification_method' => 'direct_confirmation',
            'editorial_status' => 'published',
            'published_at' => now(),
        ])->save();

        Artisan::call('db:seed', [
            '--class' => PeniaProfilePilotSeeder::class,
            '--force' => true,
        ]);

        $reviewed->refresh();
        $this->assertSame('La Cautiva revisada por un editor', $reviewed->title);
        $this->assertSame('verified', $reviewed->verification_status);
        $this->assertSame('direct_confirmation', $reviewed->verification_method);
        $this->assertSame('published', $reviewed->editorial_status);
        $this->assertSame(10, PeniaProfile::query()->whereIn('slug', PeniaProfilePilotSeeder::PILOT_SLUGS)->count());
    }
}
