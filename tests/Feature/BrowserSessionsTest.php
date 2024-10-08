<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\LogoutOtherBrowserSessionsForm;
use Livewire\Livewire;
use Tests\TestCase;

class BrowserSessionsTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_other_browser_sessions_can_be_logged_out()
=======
    public function test_other_browser_sessions_can_be_logged_out(): void
>>>>>>> dev
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(LogoutOtherBrowserSessionsForm::class)
<<<<<<< HEAD
                ->set('password', 'password')
                ->call('logoutOtherBrowserSessions');
=======
            ->set('password', 'password')
            ->call('logoutOtherBrowserSessions')
            ->assertSuccessful();
>>>>>>> dev
    }
}
