<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Solicitudes de alta de ficha: el usuario no encuentra su artista/festival y pide que se cree.
 * Reutiliza entity_claim_requests: entity_id pasa a ser opcional (se completa al aprobar) y se
 * agregan el tipo de solicitud, el nombre propuesto, el link de respaldo y datos propuestos.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('entity_claim_requests') || Schema::hasColumn('entity_claim_requests', 'kind')) {
            return;
        }

        Schema::table('entity_claim_requests', function (Blueprint $table) {
            $table->string('kind', 10)->default('link')->after('user_id');
            $table->string('proposed_name')->nullable()->after('entity_id');
            $table->string('evidence_url', 2048)->nullable()->after('message');
            $table->json('proposed_data')->nullable()->after('evidence_url');
            $table->index(['kind', 'status'], 'claims_kind_status_idx');
        });

        DB::statement('ALTER TABLE entity_claim_requests MODIFY entity_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('entity_claim_requests', 'kind')) {
            return;
        }

        DB::table('entity_claim_requests')->where('kind', 'create')->whereNull('entity_id')->delete();
        DB::statement('ALTER TABLE entity_claim_requests MODIFY entity_id BIGINT UNSIGNED NOT NULL');

        Schema::table('entity_claim_requests', function (Blueprint $table) {
            $table->dropIndex('claims_kind_status_idx');
            $table->dropColumn(['kind', 'proposed_name', 'evidence_url', 'proposed_data']);
        });
    }
};
