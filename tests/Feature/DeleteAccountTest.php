<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_user_accounts_can_be_deleted()
    {
        if (! Features::hasAccountDeletionFeatures()) {
            return $this->markTestSkipped('Account deletion is not enabled.');
=======
    public function test_user_accounts_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
>>>>>>> dev
        }

        $this->actingAs($user = User::factory()->create());

        $component = Livewire::test(DeleteUserForm::class)
<<<<<<< HEAD
                        ->set('password', 'password')
                        ->call('deleteUser');
=======
            ->set('password', 'password')
            ->call('deleteUser');
>>>>>>> dev

        $this->assertNull($user->fresh());
    }

<<<<<<< HEAD
    public function test_correct_password_must_be_provided_before_account_can_be_deleted()
    {
        if (! Features::hasAccountDeletionFeatures()) {
            return $this->markTestSkipped('Account deletion is not enabled.');
=======
    public function test_correct_password_must_be_provided_before_account_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
>>>>>>> dev
        }

        $this->actingAs($user = User::factory()->create());

        Livewire::test(DeleteUserForm::class)
<<<<<<< HEAD
                        ->set('password', 'wrong-password')
                        ->call('deleteUser')
                        ->assertHasErrors(['password']);
=======
            ->set('password', 'wrong-password')
            ->call('deleteUser')
            ->assertHasErrors(['password']);
>>>>>>> dev

        $this->assertNotNull($user->fresh());
    }
}
