<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassifiedImagesTable extends Migration
{
    public function up()
    {
        Schema::create('classified_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classified_id')->constrained('classifieds')->onDelete('cascade');
            $table->string('image_path');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('classified_images');
    }
}
