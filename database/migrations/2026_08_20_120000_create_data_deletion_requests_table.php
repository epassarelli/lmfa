<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->string('confirmation_code', 64)->unique();
            $table->string('provider', 50);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->char('external_user_hash', 64)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('error_detail', 120)->nullable();
            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->unique(['provider', 'external_user_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_deletion_requests');
    }
};
