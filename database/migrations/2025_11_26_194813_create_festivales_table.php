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
        Schema::create('festivales', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('provincia_id');
            $table->integer('mes_id');
            $table->string('titulo', 150);
            $table->string('slug', 150);
            $table->text('detalle');
            $table->string('foto', 150)->nullable();
            $table->integer('visitas')->default(0);
            $table->integer('user_id')->default(1);
            $table->boolean('estado')->default(false);
            $table->dateTime('publicar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('festivales');
    }
};
