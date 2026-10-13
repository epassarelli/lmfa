<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PrivacyPolicyNewsletterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_privacy_policy_covers_the_newsletter(): void
    {
        $this->get(route('legal.privacy'))->assertOk()
            ->assertSee('9. Newsletter')
            ->assertSee('doble confirmacion')
            ->assertSee('Baja en cualquier momento')
            ->assertSee('Medicion de enlaces')
            ->assertSee('no usamos pixeles de apertura')
            ->assertSee('huella cifrada')
            ->assertSee('Agencia de Acceso a la Informacion Publica')
            ->assertSee('13 de octubre de 2026');
    }

    public function test_the_other_legal_pages_keep_their_own_date(): void
    {
        $this->get(route('legal.terms'))->assertOk()->assertDontSee('13 de octubre de 2026');
    }

    public function test_what_the_policy_promises_matches_what_the_system_does(): void
    {
        // «No usamos pixeles de apertura»: el correo no incluye imágenes de seguimiento.
        $edition = \App\Models\NewsletterEdition::create([
            'edition_key' => 'privacy-'.uniqid(), 'subject' => 'x', 'status' => 'approved',
            'period_start' => now()->subWeek()->toDateString(), 'period_end' => now()->toDateString(),
        ]);
        $subscriber = new \App\Models\NewsletterSubscriber(['email' => 'p@example.test', 'token' => 'abc']);

        $html = (new \App\Mail\NewsletterEditionMail($edition, $subscriber, []))->render();
        $this->assertStringNotContainsString('<img', $html);

        // «No registramos quién hizo cada clic ni su IP»: la tabla de clics no tiene datos personales.
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('newsletter_links');
        $this->assertSame([], array_values(array_intersect($columns, ['subscriber_id', 'user_id', 'email', 'ip', 'ip_hash', 'user_agent'])));

        // «Guardamos la IP cifrada, no en claro»: solo existe el hash.
        $this->assertContains('consent_ip_hash', \Illuminate\Support\Facades\Schema::getColumnListing('newsletter_subscribers'));
        $this->assertNotContains('ip', \Illuminate\Support\Facades\Schema::getColumnListing('newsletter_subscribers'));
    }
}
