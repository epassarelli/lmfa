<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassifiedTagTable extends Migration
{
    public function up()
    {
        Schema::create('classified_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classified_id')->constrained('classifieds')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('classified_tag');
    }
}
