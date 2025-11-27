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
        Schema::create('interpretes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('telefono', 30)->nullable();
            $table->string('correo', 30)->nullable();
            $table->string('facebook', 50)->nullable();
            $table->string('youtube', 60)->nullable();
            $table->string('twitter', 50)->nullable();
            $table->string('instagram', 50)->nullable();
            $table->string('interprete', 50);
            $table->string('slug', 50)->nullable();
            $table->text('biografia');
            $table->string('foto', 65)->nullable();
            $table->integer('user_id')->default(1);
            $table->integer('visitas')->default(0);
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
        Schema::dropIfExists('interpretes');
    }
};
