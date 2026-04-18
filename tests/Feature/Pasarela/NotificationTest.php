<?php

namespace Tests\Feature\Pasarela;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * PC-12-HU-01: Tests del sistema de notificaciones al publicador.
 */
class NotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_redirected(): void
    {
        $this->get(route('pasarela.notifications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_sees_own_notifications(): void
    {
        $user = User::factory()->create();

        UserNotification::notify($user->id, 'publication.success', 'Publicado en portal', 'Tu evento fue publicado.');
        UserNotification::notify($user->id, 'event.reminder', 'Recordatorio', 'Tu evento es mañana.');

        $this->actingAs($user)
            ->get(route('pasarela.notifications.index'))
            ->assertOk()
            ->assertViewIs('pasarela.notifications.index')
            ->assertSee('Publicado en portal')
            ->assertSee('Recordatorio');
    }

    public function test_user_does_not_see_others_notifications(): void
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();

        UserNotification::notify($otherUser->id, 'publication.success', 'Notif ajena', 'No deberías ver esto.');

        $this->actingAs($user)
            ->get(route('pasarela.notifications.index'))
            ->assertOk()
            ->assertDontSee('Notif ajena');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();

        $notification = UserNotification::notify($user->id, 'test', 'Test', 'Cuerpo');
        $this->assertFalse((bool) $notification->fresh()->is_read);

        $this->actingAs($user)
            ->post(route('pasarela.notifications.mark-read', $notification))
            ->assertRedirect();

        $this->assertTrue($notification->fresh()->is_read);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_others_notification_as_read(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $user      = User::factory()->create();
        $otherUser = User::factory()->create();

        $notification = UserNotification::notify($otherUser->id, 'test', 'Test', 'Ajena');

        $this->actingAs($user)
            ->post(route('pasarela.notifications.mark-read', $notification));
    }

    public function test_mark_all_read_works(): void
    {
        $user = User::factory()->create();

        UserNotification::notify($user->id, 'a', 'A', 'Body A');
        UserNotification::notify($user->id, 'b', 'B', 'Body B');
        UserNotification::notify($user->id, 'c', 'C', 'Body C');

        $this->actingAs($user)
            ->post(route('pasarela.notifications.mark-all-read'))
            ->assertRedirect();

        $unread = UserNotification::where('user_id', $user->id)->where('is_read', false)->count();
        $this->assertEquals(0, $unread);
    }

    public function test_unread_count_endpoint_returns_json(): void
    {
        $user = User::factory()->create();

        UserNotification::notify($user->id, 'x', 'X', 'Body');
        UserNotification::notify($user->id, 'y', 'Y', 'Body');

        $this->actingAs($user)
            ->getJson(route('pasarela.notifications.unread-count'))
            ->assertOk()
            ->assertJsonPath('count', 2);
    }
}
