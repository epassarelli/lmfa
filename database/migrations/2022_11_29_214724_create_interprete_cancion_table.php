<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterpreteCancionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interprete_cancion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('interprete_id')
                ->nullable()
                ->constrained('interpretes')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('cancion_id')
                ->nullable()
                ->constrained('canciones')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interprete_cancion');
    }
}
