<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('festivales', function (Blueprint $table) {
            $table->string('image_alt', 255)->nullable()->after('featured_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('festivales', function (Blueprint $table) {
            $table->dropColumn('image_alt');
        });
    }
};
