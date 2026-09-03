<?php

use Illuminate\Database\Migrations\Migration;
return new class extends Migration
{
    public function up(): void
    {
        // Intencionalmente no destructiva. El retiro funcional del legacy no implica
        // borrar su tabla ni el puente de trazabilidad antes del backfill validado.
    }

    public function down(): void
    {
        // No-op simétrico: un rollback tampoco debe destruir datos legacy.
    }
};
