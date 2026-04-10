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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type', 100);
            $table->unsignedBigInteger('owner_id');
            $table->string('provider', 50); // e.g. facebook, instagram, twitter
            $table->string('account_name');
            $table->string('account_external_id');
            $table->string('page_or_profile_name')->nullable();
            
            // Tokens should invariably be large for encryption
            $table->text('token_encrypted');
            $table->text('refresh_token_encrypted')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            
            $table->json('scopes_json')->nullable();
            $table->string('status', 30)->default('active'); // active, expired, disconnected
            $table->timestamp('last_checked_at')->nullable();
            
            $table->timestamps();

            // We omit creating a composite index here to circumvent MariaDB's crashing bug with altering large indices
            // We can add them later or manually if necessary.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
