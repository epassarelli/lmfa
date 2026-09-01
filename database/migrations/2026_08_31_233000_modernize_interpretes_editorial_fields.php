<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interpretes', function (Blueprint $table) {
            $table->string('artist_type', 20)->nullable()->after('interprete');
            $table->text('excerpt')->nullable()->after('biografia');
            $table->string('seo_title', 255)->nullable()->after('excerpt');
            $table->string('meta_description', 320)->nullable()->after('seo_title');
            $table->string('image_alt', 255)->nullable()->after('foto');
            $table->string('web', 255)->nullable()->after('instagram');
        });
    }

    public function down(): void
    {
        Schema::table('interpretes', function (Blueprint $table) {
            $table->dropColumn([
                'artist_type',
                'excerpt',
                'seo_title',
                'meta_description',
                'image_alt',
                'web',
            ]);
        });
    }
};
