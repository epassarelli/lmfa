<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penias')) {
            Schema::create('penias', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('titulo');
                $table->string('slug');
                $table->text('detalle');
                $table->string('foto');
                $table->unsignedBigInteger('user_id')->nullable()->index('penias_user_id_foreign');
                $table->integer('visitas');
                $table->dateTime('publicar')->nullable();
                $table->integer('estado');
                $table->timestamps();

                $table->foreign('user_id', 'penias_user_id_foreign')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('penia_profiles') && ! Schema::hasColumn('penia_profiles', 'legacy_penia_id')) {
            Schema::table('penia_profiles', function (Blueprint $table) {
                $table->unsignedBigInteger('legacy_penia_id')->nullable()->after('id');
                $table->unique('legacy_penia_id', 'penia_profiles_legacy_penia_id_unique');
                $table->foreign('legacy_penia_id', 'penia_profiles_legacy_penia_id_foreign')
                    ->references('id')
                    ->on('penias')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Preservación deliberada: nunca eliminar datos legacy durante rollback.
    }
};
