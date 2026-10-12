<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mensajes del formulario de contacto (spec contact-form-overhaul).
 *
 * Defensiva: el modelo suponía una tabla heredada que ninguna migración creaba. Si la tabla no
 * existe se crea completa; si existe (p. ej. en producción) solo se AGREGAN las columnas que falten,
 * sin modificar ni borrar nada de lo que ya tiene.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('email');
                $table->string('asunto');
                $table->text('mensaje');
                $table->timestamp('fecha_envio')->nullable();
            });
        }

        $additions = [
            'status' => fn (Blueprint $t) => $t->string('status', 20)->default('new')->index(),
            'user_id' => fn (Blueprint $t) => $t->unsignedBigInteger('user_id')->nullable()->index(),
            'ip_hash' => fn (Blueprint $t) => $t->string('ip_hash', 64)->nullable(),
            'read_at' => fn (Blueprint $t) => $t->timestamp('read_at')->nullable(),
            'answered_at' => fn (Blueprint $t) => $t->timestamp('answered_at')->nullable(),
            'spam_reason' => fn (Blueprint $t) => $t->string('spam_reason', 100)->nullable(),
        ];

        foreach ($additions as $column => $definition) {
            if (! Schema::hasColumn('contact_messages', $column)) {
                Schema::table('contact_messages', fn (Blueprint $table) => $definition($table));
            }
        }
    }

    /**
     * Solo retira las columnas que agrega esta migración; no borra la tabla ni los mensajes
     * (puede ser una tabla heredada).
     */
    public function down(): void
    {
        if (! Schema::hasTable('contact_messages')) {
            return;
        }

        foreach (['status', 'user_id', 'ip_hash', 'read_at', 'answered_at', 'spam_reason'] as $column) {
            if (Schema::hasColumn('contact_messages', $column)) {
                Schema::table('contact_messages', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
    }
};
