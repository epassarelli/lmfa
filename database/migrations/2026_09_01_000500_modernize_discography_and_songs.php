<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albunes', function (Blueprint $table) {
            $table->string('album_type', 30)->nullable()->after('album');
            $table->text('excerpt')->nullable()->after('anio');
            $table->string('label', 255)->nullable()->after('excerpt');
            $table->date('release_date')->nullable()->after('label');
            $table->string('seo_title', 255)->nullable()->after('release_date');
            $table->string('meta_description', 320)->nullable()->after('seo_title');
            $table->string('image_alt', 255)->nullable()->after('foto');
        });

        Schema::table('canciones', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('letra');
            $table->string('composer', 255)->nullable()->after('excerpt');
            $table->string('lyricist', 255)->nullable()->after('composer');
            $table->string('rights_status', 30)->nullable()->after('lyricist');
            $table->string('lyrics_source_url', 2048)->nullable()->after('rights_status');
            $table->boolean('is_instrumental')->default(false)->after('lyrics_source_url');
            $table->string('seo_title', 255)->nullable()->after('spotify');
            $table->string('meta_description', 320)->nullable()->after('seo_title');
        });

        DB::statement('ALTER TABLE canciones MODIFY letra TEXT NULL');
    }

    public function down(): void
    {
        Schema::table('albunes', function (Blueprint $table) {
            $table->dropColumn([
                'album_type',
                'excerpt',
                'label',
                'release_date',
                'seo_title',
                'meta_description',
                'image_alt',
            ]);
        });

        Schema::table('canciones', function (Blueprint $table) {
            $table->dropColumn([
                'excerpt',
                'composer',
                'lyricist',
                'rights_status',
                'lyrics_source_url',
                'is_instrumental',
                'seo_title',
                'meta_description',
            ]);
        });

        // No se fuerza letra a NOT NULL en rollback: luego del release pueden existir
        // fichas legítimas sin letra y endurecer la columna podría destruir datos.
    }
};
