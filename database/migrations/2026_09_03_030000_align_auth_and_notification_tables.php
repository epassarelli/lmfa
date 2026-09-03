<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('password_resets') && ! Schema::hasTable('password_reset_tokens')) {
            Schema::rename('password_resets', 'password_reset_tokens');
        } elseif (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (Schema::hasTable('notifications') && ! Schema::hasTable('user_notifications')) {
            Schema::rename('notifications', 'user_notifications');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_notifications') && ! Schema::hasTable('notifications')) {
            Schema::rename('user_notifications', 'notifications');
        }

        if (Schema::hasTable('password_reset_tokens') && ! Schema::hasTable('password_resets')) {
            Schema::rename('password_reset_tokens', 'password_resets');
        }
    }
};
