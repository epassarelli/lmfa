<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comidas', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('receta');
            $table->json('ingredients')->nullable()->after('excerpt');
            $table->json('instructions')->nullable()->after('ingredients');
            $table->unsignedSmallInteger('prep_time_minutes')->nullable()->after('instructions');
            $table->unsignedSmallInteger('cook_time_minutes')->nullable()->after('prep_time_minutes');
            $table->string('servings', 100)->nullable()->after('cook_time_minutes');
            $table->string('region', 150)->nullable()->after('servings');
            $table->string('seo_title', 255)->nullable()->after('region');
            $table->string('meta_description', 320)->nullable()->after('seo_title');
            $table->string('image_alt', 255)->nullable()->after('foto');
        });

        DB::statement(
            'ALTER TABLE comidas
                MODIFY foto VARCHAR(255) NULL,
                MODIFY visitas INT NOT NULL DEFAULT 0,
                MODIFY estado INT NOT NULL DEFAULT 0'
        );
    }

    public function down(): void
    {
        Schema::table('comidas', function (Blueprint $table) {
            $table->dropColumn([
                'excerpt',
                'ingredients',
                'instructions',
                'prep_time_minutes',
                'cook_time_minutes',
                'servings',
                'region',
                'seo_title',
                'meta_description',
                'image_alt',
            ]);
        });

        // No se vuelve foto a NOT NULL ni se eliminan defaults para evitar
        // invalidar registros creados durante el período moderno.
    }
};
