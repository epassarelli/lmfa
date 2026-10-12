<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retiro definitivo del flujo "Publicar / Contribuir" (spec remove-contribuir-flow).
 *
 * Elimina la tabla `contributions` y las columnas `users.points` / `users.rank`, que solo se
 * alimentaban al aprobar contribuciones. El usuario confirmó que producción no tiene contribuciones;
 * en local había 1 fila de prueba y ningún usuario con puntos. down() recrea la estructura (no los datos).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('contributions');

        foreach (['points', 'rank'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('contributions')) {
            Schema::create('contributions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('contributable_type');
                $table->unsignedBigInteger('contributable_id')->nullable();
                $table->json('payload');
                $table->enum('status', ['pending', 'approved', 'rejected', 'auto-applied'])->default('pending');
                $table->text('moderator_comment')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('users', 'points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('points')->default(0);
                $table->string('rank')->default('Colaborador Bronce');
            });
        }
    }
};
