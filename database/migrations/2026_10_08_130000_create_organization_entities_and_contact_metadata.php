<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organization_entities')) {
            Schema::create('organization_entities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('entity_type', 100);
                $table->unsignedBigInteger('entity_id');
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->unique(['organization_id', 'entity_type', 'entity_id'], 'org_entities_unique');
                $table->index(['entity_type', 'entity_id'], 'org_entities_entity_idx');
            });
        }

        Schema::table('organizations', function (Blueprint $table) {
            if (! Schema::hasColumn('organizations', 'contact_consent_at')) {
                $table->timestamp('contact_consent_at')->nullable()->after('is_verified');
            }
            if (! Schema::hasColumn('organizations', 'contact_consent_purpose')) {
                $table->string('contact_consent_purpose', 100)->nullable()->after('contact_consent_at');
            }
            if (! Schema::hasColumn('organizations', 'unsubscribed_at')) {
                $table->timestamp('unsubscribed_at')->nullable()->after('contact_consent_purpose');
            }
            if (! Schema::hasColumn('organizations', 'source')) {
                $table->string('source', 50)->nullable()->after('unsubscribed_at');
            }
            if (! Schema::hasColumn('organizations', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('source');
            }
            if (! Schema::hasColumn('organizations', 'verification_method')) {
                $table->string('verification_method', 30)->nullable()->after('verified_at');
            }
            if (! Schema::hasColumn('organizations', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('verification_method')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'verified_by')) {
                $table->dropForeign(['verified_by']);
            }

            $columns = array_filter([
                'contact_consent_at',
                'contact_consent_purpose',
                'unsubscribed_at',
                'source',
                'verified_at',
                'verification_method',
                'verified_by',
            ], fn (string $column) => Schema::hasColumn('organizations', $column));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::dropIfExists('organization_entities');
    }
};
