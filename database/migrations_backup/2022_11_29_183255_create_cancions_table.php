<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCancionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('canciones', function (Blueprint $table) {
            $table->id();

            $table->string('cancion');
            $table->string('slug');
            $table->text('letra');
            $table->string('youtube');
            $table->string('spotify');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('interprete_id')
                ->nullable()
                ->constrained('interpretes')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->integer('visitas');

            $table->datetime('publicar');
            $table->integer('estado');

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
        Schema::dropIfExists('canciones');
    }
}
