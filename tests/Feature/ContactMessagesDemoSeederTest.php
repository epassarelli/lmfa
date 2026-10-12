<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Database\Seeders\ContactMessagesDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ContactMessagesDemoSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_creates_demo_messages_covering_every_status_and_more_than_a_page(): void
    {
        $this->seed(ContactMessagesDemoSeeder::class);

        $demo = ContactMessage::where('nombre', 'like', ContactMessagesDemoSeeder::PREFIX.'%');
        $this->assertGreaterThan(25, $demo->count(), 'más de una página para probar la paginación');

        foreach (['new', 'read', 'answered', 'spam'] as $status) {
            $this->assertGreaterThan(0, (clone $demo)->where('status', $status)->count(), "falta un mensaje en estado $status");
        }

        $spam = (clone $demo)->where('status', 'spam')->first();
        $this->assertSame('too_many_links', $spam->spam_reason);
        $this->assertNotNull((clone $demo)->where('status', 'answered')->first()->answered_at);
        $this->assertTrue((clone $demo)->where('mensaje', 'like', '%<script>%')->exists(), 'incluye HTML para verificar el escape');
        $this->assertTrue((clone $demo)->whereRaw('LENGTH(mensaje) > 1000')->exists(), 'incluye un mensaje largo');
    }

    public function test_it_is_idempotent_and_only_uses_example_domains(): void
    {
        $this->seed(ContactMessagesDemoSeeder::class);
        $count = ContactMessage::count();

        $this->seed(ContactMessagesDemoSeeder::class);

        $this->assertSame($count, ContactMessage::count());
        $this->assertSame(0, ContactMessage::where('nombre', 'like', ContactMessagesDemoSeeder::PREFIX.'%')->where('email', 'not like', '%@example.test')->count());
    }

    public function test_it_refuses_to_run_outside_local_environments(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        (new ContactMessagesDemoSeeder())->run();

        $this->assertSame(0, ContactMessage::where('nombre', 'like', ContactMessagesDemoSeeder::PREFIX.'%')->count());
    }
}
