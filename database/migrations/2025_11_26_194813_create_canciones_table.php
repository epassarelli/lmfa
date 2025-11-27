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
        Schema::create('canciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cancion');
            $table->string('slug');
            $table->text('letra');
            $table->string('youtube')->nullable();
            $table->string('spotify')->nullable();
            $table->unsignedBigInteger('interprete_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index('canciones_user_id_foreign');
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
        Schema::dropIfExists('canciones');
    }
};
