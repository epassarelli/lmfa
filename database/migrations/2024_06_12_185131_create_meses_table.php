<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMesesTable extends Migration
{
    public function up()
    {
        Schema::create('meses', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meses');
    }
}
