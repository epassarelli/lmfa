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
        Schema::create('noticias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('titulo');
            $table->string('slug');
            $table->text('noticia');
            $table->string('foto');
            $table->unsignedBigInteger('user_id')->nullable()->index('noticias_user_id_foreign');
            $table->unsignedBigInteger('interprete_id')->nullable()->index('noticias_interprete_id_foreign');
            $table->integer('visitas')->default(0);
            $table->dateTime('publicar')->nullable();
            $table->integer('categoria_id')->default(1);
            $table->integer('estado')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }
};
