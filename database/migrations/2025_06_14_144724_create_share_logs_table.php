<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('share_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_contenido');
            $table->unsignedBigInteger('contenido_id');
            $table->string('titulo');
            $table->text('url');
            $table->string('red');
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_logs');
    }
};
