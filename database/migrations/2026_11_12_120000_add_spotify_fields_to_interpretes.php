<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec: project/docs/specs/spotify-artistas-discos.md
 * Aditiva: no toca datos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interpretes', function (Blueprint $table) {
            $table->string('spotify_artist_id', 32)->nullable()->unique()->after('web');
            $table->timestamp('spotify_link_checked_at')->nullable()->after('spotify_artist_id');
            $table->timestamp('spotify_albums_checked_at')->nullable()->after('spotify_link_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('interpretes', function (Blueprint $table) {
            $table->dropUnique(['spotify_artist_id']);
            $table->dropColumn(['spotify_artist_id', 'spotify_link_checked_at', 'spotify_albums_checked_at']);
        });
    }
};
