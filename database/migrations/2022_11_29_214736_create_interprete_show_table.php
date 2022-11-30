<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterpreteShowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interprete_show', function (Blueprint $table) {
            $table->id();

            $table->foreignId('interprete_id')
                ->nullable()
                ->constrained('interpretes')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('show_id')
                ->nullable()
                ->constrained('shows')
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
        Schema::dropIfExists('interprete_show');
    }
}
