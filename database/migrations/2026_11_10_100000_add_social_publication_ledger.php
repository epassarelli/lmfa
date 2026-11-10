<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::table('publication_attempts')->select('publication_target_id', 'attempt_number')->groupBy('publication_target_id', 'attempt_number')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Hay números de intento duplicados. Revisar antes de migrar.');
        }
        if (!Schema::hasTable('social_publication_eligibilities')) Schema::create('social_publication_eligibilities', function (Blueprint $t) {
            $t->id();
            $t->string('content_kind', 32);
            $t->unsignedBigInteger('content_id');
            $t->string('provider', 16);
            $t->string('destination_key', 96);
            $t->boolean('enabled')->default(false);
            $t->boolean('portal_only_override')->default(false);
            $t->foreignId('enabled_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('disabled_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('enabled_at')->nullable();
            $t->timestamp('disabled_at')->nullable();
            $t->timestamps();
            $t->unique(['content_kind', 'content_id', 'provider', 'destination_key'], 'social_eligibility_identity');
            $t->index(['destination_key', 'enabled', 'id'], 'social_eligibility_candidates');
        });
        if (!Schema::hasColumn('publication_targets', 'executor')) Schema::table('publication_targets', function (Blueprint $t) {
            $t->string('ledger_content_kind', 32)->nullable();
            $t->unsignedBigInteger('ledger_content_id')->nullable();
            $t->string('destination_key', 96)->nullable();
            $t->string('executor', 16)->default('laravel');
            $t->timestamp('published_at')->nullable();
            $t->string('external_post_id', 128)->nullable();
            $t->string('external_url', 512)->nullable();
            $t->string('lease_token_hash', 64)->nullable();
            $t->timestamp('lease_expires_at')->nullable();
            $t->timestamp('retry_after')->nullable();
            $t->unsignedInteger('attempts_count')->default(0);
            $t->string('last_error_code', 64)->nullable();
            $t->string('last_error_message', 512)->nullable();
            $t->string('content_hash', 64)->nullable();
            $t->json('payload_snapshot_json')->nullable();
            $t->string('template_id', 64)->nullable();
            $t->string('template_catalog_version', 16)->nullable();
            $t->string('asset_sha256', 64)->nullable();
            $t->string('asset_reference', 512)->nullable();
            $t->unique(['ledger_content_kind', 'ledger_content_id', 'provider', 'destination_key'], 'social_target_identity');
            $t->unique(['provider', 'destination_key', 'external_post_id'], 'social_remote_identity');
            $t->index(['executor', 'status', 'lease_expires_at'], 'social_target_recovery');
        });
        if (!Schema::hasColumn('publication_attempts', 'executor')) Schema::table('publication_attempts', function (Blueprint $t) {
            $t->uuid('request_key')->nullable()->unique();
            $t->string('executor', 16)->default('laravel');
            $t->string('n8n_execution_id', 96)->nullable();
            $t->timestamp('lease_expires_at')->nullable();
            $t->timestamp('dispatch_started_at')->nullable();
            $t->string('outcome_fingerprint', 64)->nullable();
            $t->unique(['publication_target_id', 'attempt_number'], 'social_attempt_sequence');
        });
        // Native ALTER avoids adding doctrine/dbal solely for this MariaDB change.
        DB::statement('ALTER TABLE publication_requests DROP FOREIGN KEY publication_requests_requested_by_foreign, MODIFY requested_by BIGINT UNSIGNED NULL, ADD CONSTRAINT social_request_author_foreign FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        throw new RuntimeException('Rollback automático deshabilitado: conservar historial. Preparar una migración de reversión revisada.');
    }
};
