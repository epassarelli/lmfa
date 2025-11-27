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
        Schema::create('albunes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('interprete_id');
            $table->date('publicar')->nullable()->useCurrent();
            $table->string('album', 80);
            $table->string('slug', 50);
            $table->text('spotify')->nullable();
            $table->string('anio', 4);
            $table->string('foto', 100)->nullable();
            $table->integer('visitas')->default(0);
            $table->integer('user_id')->default(1);
            $table->boolean('estado')->default(false);
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albunes');
    }
};
