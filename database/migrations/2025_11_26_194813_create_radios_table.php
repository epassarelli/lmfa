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
        Schema::create('radios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('titulo');
            $table->string('slug');
            $table->text('detalle');
            $table->string('foto');
            $table->string('escucharOnline');
            $table->unsignedBigInteger('user_id')->nullable()->index('radios_user_id_foreign');
            $table->integer('visitas');
            $table->dateTime('publicar')->nullable();
            $table->integer('estado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radios');
    }
};
