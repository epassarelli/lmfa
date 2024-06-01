<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
<<<<<<< HEAD
=======
use Laravel\Fortify\Features;
>>>>>>> dev
use Laravel\Jetstream\Http\Livewire\TwoFactorAuthenticationForm;
use Livewire\Livewire;
use Tests\TestCase;

class TwoFactorAuthenticationSettingsTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_two_factor_authentication_can_be_enabled()
    {
=======
    public function test_two_factor_authentication_can_be_enabled(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

>>>>>>> dev
        $this->actingAs($user = User::factory()->create());

        $this->withSession(['auth.password_confirmed_at' => time()]);

        Livewire::test(TwoFactorAuthenticationForm::class)
<<<<<<< HEAD
                ->call('enableTwoFactorAuthentication');
=======
            ->call('enableTwoFactorAuthentication');
>>>>>>> dev

        $user = $user->fresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertCount(8, $user->recoveryCodes());
    }

<<<<<<< HEAD
    public function test_recovery_codes_can_be_regenerated()
    {
=======
    public function test_recovery_codes_can_be_regenerated(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

>>>>>>> dev
        $this->actingAs($user = User::factory()->create());

        $this->withSession(['auth.password_confirmed_at' => time()]);

        $component = Livewire::test(TwoFactorAuthenticationForm::class)
<<<<<<< HEAD
                ->call('enableTwoFactorAuthentication')
                ->call('regenerateRecoveryCodes');
=======
            ->call('enableTwoFactorAuthentication')
            ->call('regenerateRecoveryCodes');
>>>>>>> dev

        $user = $user->fresh();

        $component->call('regenerateRecoveryCodes');

        $this->assertCount(8, $user->recoveryCodes());
        $this->assertCount(8, array_diff($user->recoveryCodes(), $user->fresh()->recoveryCodes()));
    }

<<<<<<< HEAD
    public function test_two_factor_authentication_can_be_disabled()
    {
=======
    public function test_two_factor_authentication_can_be_disabled(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

>>>>>>> dev
        $this->actingAs($user = User::factory()->create());

        $this->withSession(['auth.password_confirmed_at' => time()]);

        $component = Livewire::test(TwoFactorAuthenticationForm::class)
<<<<<<< HEAD
                ->call('enableTwoFactorAuthentication');
=======
            ->call('enableTwoFactorAuthentication');
>>>>>>> dev

        $this->assertNotNull($user->fresh()->two_factor_secret);

        $component->call('disableTwoFactorAuthentication');

        $this->assertNull($user->fresh()->two_factor_secret);
    }
}
