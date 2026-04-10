<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('publication_requests', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 100);
            $table->unsignedBigInteger('content_id');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            
            $table->string('mode', 50); // enum from publication_mode
            $table->boolean('wants_portal_publish')->default(false);
            $table->boolean('wants_portal_social')->default(false);
            $table->boolean('wants_own_social')->default(false);
            
            $table->timestamp('scheduled_at')->nullable();
            $table->string('reminder_policy', 50)->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();
            
            // Omitting complex indexes due to MariaDB issues, simple indexes are ok.
            // $table->index(['content_type', 'content_id']); // This might fail on MariaDB depending on charset. We'll skip for safety.
        });

        Schema::create('publication_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_request_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50); // e.g., facebook, native_portal
            
            // Can be null if publishing to native portal without account
            $table->foreignId('social_account_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('destination_type', 50)->nullable(); // page, group, feed, story
            $table->string('template_variant', 50)->nullable();
            
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status', 30)->default('pending');
            $table->integer('priority')->default(0);
            
            $table->timestamps();
        });

        Schema::create('publication_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_target_id')->constrained()->cascadeOnDelete();
            $table->integer('attempt_number')->default(1);
            
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            
            $table->json('request_payload_json')->nullable();
            $table->json('response_payload_json')->nullable();
            
            $table->string('external_post_id')->nullable();
            $table->string('external_url')->nullable();
            
            $table->string('status', 30)->default('processing'); // success, failed, processing
            
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('is_retryable')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publication_attempts');
        Schema::dropIfExists('publication_targets');
        Schema::dropIfExists('publication_requests');
    }
};
