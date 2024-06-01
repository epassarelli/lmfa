<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\Http\Livewire\UpdatePasswordForm;
use Livewire\Livewire;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_password_can_be_updated()
=======
    public function test_password_can_be_updated(): void
>>>>>>> dev
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdatePasswordForm::class)
<<<<<<< HEAD
                ->set('state', [
                    'current_password' => 'password',
                    'password' => 'new-password',
                    'password_confirmation' => 'new-password',
                ])
                ->call('updatePassword');
=======
            ->set('state', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->call('updatePassword');
>>>>>>> dev

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

<<<<<<< HEAD
    public function test_current_password_must_be_correct()
=======
    public function test_current_password_must_be_correct(): void
>>>>>>> dev
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdatePasswordForm::class)
<<<<<<< HEAD
                ->set('state', [
                    'current_password' => 'wrong-password',
                    'password' => 'new-password',
                    'password_confirmation' => 'new-password',
                ])
                ->call('updatePassword')
                ->assertHasErrors(['current_password']);
=======
            ->set('state', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);
>>>>>>> dev

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

<<<<<<< HEAD
    public function test_new_passwords_must_match()
=======
    public function test_new_passwords_must_match(): void
>>>>>>> dev
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdatePasswordForm::class)
<<<<<<< HEAD
                ->set('state', [
                    'current_password' => 'password',
                    'password' => 'new-password',
                    'password_confirmation' => 'wrong-password',
                ])
                ->call('updatePassword')
                ->assertHasErrors(['password']);
=======
            ->set('state', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'wrong-password',
            ])
            ->call('updatePassword')
            ->assertHasErrors(['password']);
>>>>>>> dev

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
