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
        Schema::create('shows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('show');
            $table->string('slug')->nullable();
            $table->text('detalle');
            $table->string('foto')->nullable();
            $table->dateTime('fecha');
            $table->string('hora')->nullable();
            $table->string('lugar')->nullable();
            $table->string('direccion')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index('shows_user_id_foreign');
            $table->unsignedBigInteger('interprete_id')->nullable()->index('shows_interprete_id_foreign');
            $table->integer('visitas')->default(0);
            $table->dateTime('publicar')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shows');
    }
};
