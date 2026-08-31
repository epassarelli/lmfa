<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mitos', function (Blueprint $table) {
            $table->string('content_type', 30)->nullable()->after('titulo');
            $table->text('excerpt')->nullable()->after('mito');
            $table->string('region', 150)->nullable()->after('excerpt');
            $table->string('seo_title', 255)->nullable()->after('region');
            $table->string('meta_description', 320)->nullable()->after('seo_title');
            $table->string('image_alt', 255)->nullable()->after('foto');
        });

        DB::statement(
            'ALTER TABLE mitos
                MODIFY foto VARCHAR(255) NULL,
                MODIFY visitas INT NOT NULL DEFAULT 0,
                MODIFY estado INT NOT NULL DEFAULT 0'
        );
    }

    public function down(): void
    {
        Schema::table('mitos', function (Blueprint $table) {
            $table->dropColumn([
                'content_type',
                'excerpt',
                'region',
                'seo_title',
                'meta_description',
                'image_alt',
            ]);
        });

        // No se restauran restricciones legacy incompatibles.
    }
};
