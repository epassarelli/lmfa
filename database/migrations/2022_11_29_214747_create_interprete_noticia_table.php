<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterpreteNoticiaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interprete_noticia', function (Blueprint $table) {
            $table->id();

            $table->foreignId('interprete_id')
                ->nullable()
                ->constrained('interpretes')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('noticia_id')
                ->nullable()
                ->constrained('noticias')
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
        Schema::dropIfExists('interprete_noticia');
    }
}
