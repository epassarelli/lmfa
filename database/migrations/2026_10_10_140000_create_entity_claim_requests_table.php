<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('entity_claim_requests')) {
            return;
        }

        Schema::create('entity_claim_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id');
            $table->string('relationship', 30);
            $table->text('message');
            $table->string('contact_phone', 50)->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'claims_status_idx');
            $table->index(['user_id', 'status'], 'claims_user_status_idx');
            $table->index(['entity_type', 'entity_id'], 'claims_entity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_claim_requests');
    }
};
