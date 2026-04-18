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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('status', 30)->default('active')->after('password');
            $table->boolean('is_verified_publisher')->default(false)->after('status');
            $table->string('publisher_type_default', 50)->nullable()->after('is_verified_publisher');
            $table->timestamp('last_login_at')->nullable()->after('publisher_type_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'status',
                'is_verified_publisher',
                'publisher_type_default',
                'last_login_at'
            ]);
        });
    }
};
