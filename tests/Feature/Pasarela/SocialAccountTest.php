<?php

namespace Tests\Feature\Pasarela;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * PC-06-HU-01: Tests de cuentas sociales del publicador.
 *
 * Cubre los tres casos principales:
 *  - listar cuentas del usuario
 *  - conectar una nueva cuenta
 *  - desconectar una cuenta
 *
 * También verifica restricciones de autorización básicas.
 */
class SocialAccountTest extends TestCase
{
    use DatabaseTransactions;

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_guest_cannot_access_social_accounts_index(): void
    {
        $this->get(route('pasarela.social-accounts.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_social_accounts_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pasarela.social-accounts.index'))
            ->assertOk()
            ->assertViewIs('pasarela.social_accounts.index');
    }

    public function test_user_only_sees_their_own_accounts(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Cuenta del usuario A
        SocialAccount::create([
            'owner_type'          => get_class($userA),
            'owner_id'            => $userA->id,
            'provider'            => 'facebook',
            'account_name'        => '@cuenta_a',
            'account_external_id' => 'fb_111',
            'token_encrypted'     => 'tok_a',
            'status'              => 'active',
        ]);

        // Cuenta del usuario B
        SocialAccount::create([
            'owner_type'          => get_class($userB),
            'owner_id'            => $userB->id,
            'provider'            => 'telegram',
            'account_name'        => '@canal_b',
            'account_external_id' => 'tg_222',
            'token_encrypted'     => 'tok_b',
            'status'              => 'active',
        ]);

        $response = $this->actingAs($userA)
            ->get(route('pasarela.social-accounts.index'));

        $response->assertOk();

        // Debe ver su propia cuenta y no la del otro usuario
        $accounts = $response->viewData('accounts');
        $this->assertCount(1, $accounts);
        $this->assertEquals('fb_111', $accounts->first()->account_external_id);
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_user_can_connect_a_social_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('pasarela.social-accounts.store'), [
                'provider'             => 'facebook',
                'account_name'         => '@mi_pagina',
                'account_external_id'  => 'fb_999',
                'page_or_profile_name' => 'Mi Folklore Arg',
                'token'                => 'EAA_fake_token_123',
                'scopes'               => 'pages_manage_posts, publish_to_groups',
            ]);

        $response->assertRedirect(route('pasarela.social-accounts.index'));

        $this->assertDatabaseHas('social_accounts', [
            'owner_type'          => get_class($user),
            'owner_id'            => $user->id,
            'provider'            => 'facebook',
            'account_name'        => '@mi_pagina',
            'account_external_id' => 'fb_999',
            'status'              => 'active',
        ]);
    }

    public function test_duplicate_account_is_rejected(): void
    {
        $user = User::factory()->create();

        $payload = [
            'provider'             => 'instagram',
            'account_name'         => '@igcuenta',
            'account_external_id'  => 'ig_555',
            'token'                => 'IGtok',
        ];

        // Primer insert OK
        $this->actingAs($user)->post(route('pasarela.social-accounts.store'), $payload);

        // Segundo insert debe rebotar
        $response = $this->actingAs($user)
            ->post(route('pasarela.social-accounts.store'), $payload);

        $response->assertRedirect(); // back()
        $this->assertEquals(
            1,
            SocialAccount::where('account_external_id', 'ig_555')->count(),
            'No debe duplicarse la cuenta'
        );
    }

    public function test_store_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('pasarela.social-accounts.store'), [])
            ->assertSessionHasErrors(['provider', 'account_name', 'account_external_id', 'token']);
    }

    public function test_store_rejects_unsupported_provider(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('pasarela.social-accounts.store'), [
                'provider'            => 'tiktok', // no soportado en MVP
                'account_name'        => '@tiktok_user',
                'account_external_id' => 'tt_001',
                'token'               => 'tok_tiktok',
            ])
            ->assertSessionHasErrors(['provider']);
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_user_can_disconnect_their_account(): void
    {
        $user = User::factory()->create();

        $account = SocialAccount::create([
            'owner_type'          => get_class($user),
            'owner_id'            => $user->id,
            'provider'            => 'telegram',
            'account_name'        => '@mi_canal',
            'account_external_id' => 'tg_777',
            'token_encrypted'     => 'tok_tg',
            'status'              => 'active',
        ]);

        $this->actingAs($user)
            ->delete(route('pasarela.social-accounts.destroy', $account))
            ->assertRedirect(route('pasarela.social-accounts.index'));

        $this->assertDatabaseMissing('social_accounts', ['id' => $account->id]);
    }

    public function test_user_cannot_disconnect_another_users_account(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $accountOfB = SocialAccount::create([
            'owner_type'          => get_class($userB),
            'owner_id'            => $userB->id,
            'provider'            => 'facebook',
            'account_name'        => '@cuenta_b',
            'account_external_id' => 'fb_b',
            'token_encrypted'     => 'tok_b',
            'status'              => 'active',
        ]);

        $this->actingAs($userA)
            ->delete(route('pasarela.social-accounts.destroy', $accountOfB))
            ->assertForbidden();

        $this->assertDatabaseHas('social_accounts', ['id' => $accountOfB->id]);
    }
}
