<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserApiTokenManagementTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function administrador_can_issue_a_bearer_token_for_a_specific_user(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('users.api-tokens.store', $targetUser->id), [
            'token_name' => 'hostinger-prod-token',
        ]);

        $response->assertRedirect(route('users.edit', $targetUser->id));
        $response->assertSessionHas('plain_text_token');

        $token = $targetUser->tokens()->where('name', 'hostinger-prod-token')->first();

        $this->assertNotNull($token);
    }

    /** @test */
    public function administrador_can_revoke_a_bearer_token_for_a_specific_user(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $targetUser = User::factory()->create();
        $plainTextToken = $targetUser->createToken('token-a-revocar')->plainTextToken;

        $tokenId = PersonalAccessToken::findToken($plainTextToken)?->id;

        $this->assertNotNull($tokenId);

        $response = $this->actingAs($admin)->delete(route('users.api-tokens.destroy', [
            'user' => $targetUser->id,
            'token' => $tokenId,
        ]));

        $response->assertRedirect(route('users.edit', $targetUser->id));
        $this->assertNull(PersonalAccessToken::find($tokenId));
    }
}
