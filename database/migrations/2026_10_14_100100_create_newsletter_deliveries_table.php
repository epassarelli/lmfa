<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de envíos: una fila por suscriptor y edición. Evita duplicados y es la base de las métricas.
     */
    public function up(): void
    {
        Schema::create('newsletter_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('newsletter_subscribers')->cascadeOnDelete();
            $table->string('edition_key', 64);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['subscriber_id', 'edition_key']);
            $table->index('edition_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_deliveries');
    }
};
