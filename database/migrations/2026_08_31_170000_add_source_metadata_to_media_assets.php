<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->text('source_url')->nullable()->after('caption');
            $table->string('source_type', 40)->nullable()->after('source_url');
            $table->string('rights_status', 40)->nullable()->after('source_type');
        });
    }

    public function down(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->dropColumn(['source_url', 'source_type', 'rights_status']);
        });
    }
};
