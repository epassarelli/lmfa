<?php

namespace Tests\Feature\Pasarela;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * PC-13-HU-01: Tests del sistema de auditoría.
 *
 * Cubre:
 *  - AuditLog::log() registra la acción correctamente
 *  - campos entity_type, entity_id, action, user_id, old/new values
 *  - helper estático funciona sin usuario autenticado
 */
class AuditLogTest extends TestCase
{
    use DatabaseTransactions;

    public function test_audit_log_records_action_with_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLog::log(
            entityType: 'App\Models\Event',
            entityId:   42,
            action:     'approved',
            oldValues:  ['editorial_status' => 'pending_review'],
            newValues:  ['editorial_status' => 'approved']
        );

        $this->assertDatabaseHas('audit_logs', [
            'user_id'     => $user->id,
            'entity_type' => 'App\Models\Event',
            'entity_id'   => 42,
            'action'      => 'approved',
        ]);

        $log = AuditLog::where('entity_id', 42)->where('action', 'approved')->first();
        $this->assertNotNull($log);
        $this->assertEquals('pending_review', $log->old_values['editorial_status']);
        $this->assertEquals('approved', $log->new_values['editorial_status']);
    }

    public function test_audit_log_works_without_authenticated_user(): void
    {
        AuditLog::log(
            entityType: 'App\Models\PublicationRequest',
            entityId:   99,
            action:     'created'
        );

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'App\Models\PublicationRequest',
            'entity_id'   => 99,
            'action'      => 'created',
            'user_id'     => null,
        ]);
    }

    public function test_audit_log_stores_timestamps(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $before = now()->subSecond();

        AuditLog::log('App\Models\News', 1, 'published');

        $log = AuditLog::where('action', 'published')->where('entity_id', 1)->first();
        $this->assertNotNull($log->created_at);
        $this->assertTrue($log->created_at->gte($before));
    }

    public function test_audit_log_empty_old_new_values_store_null(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        AuditLog::log('App\Models\Event', 5, 'viewed');

        $log = AuditLog::where('entity_id', 5)->where('action', 'viewed')->first();
        $this->assertNull($log->old_values);
        $this->assertNull($log->new_values);
    }
}
