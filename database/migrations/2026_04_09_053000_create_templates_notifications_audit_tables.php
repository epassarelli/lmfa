<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PC-08 - Publication Templates
        Schema::create('publication_templates', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 100);       // Event, News
            $table->string('provider', 50);            // facebook, instagram, telegram, native_portal
            $table->string('variant_name', 50)->default('default');
            $table->text('template_text');             // Supports {title}, {excerpt}, {url}, {date}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // PC-12 - Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 100);
            $table->string('title');
            $table->text('body');
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('read_at')->nullable();
        });

        // PC-13 - Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 50);
            $table->json('old_values_json')->nullable();
            $table->json('new_values_json')->nullable();
            $table->json('meta_json')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('publication_templates');
    }
};
