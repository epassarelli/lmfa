<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shows', function (Blueprint $table) {
            $table->string('precio_entrada')->nullable()->after('interprete_id');
            $table->string('link_entradas')->nullable()->after('precio_entrada');
            $table->boolean('destacado')->default(false)->after('link_entradas');
            $table->string('imagen_destacada')->nullable()->after('destacado');
            $table->string('slug')->unique()->after('imagen_destacada');
            $table->decimal('lat', 10, 7)->nullable()->after('slug');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->unsignedBigInteger('provincia_id')->nullable()->after('lng');

            $table->foreign('provincia_id')->references('id')->on('provincias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shows', function (Blueprint $table) {
            $table->dropForeign(['provincia_id']);
            $table->dropColumn([
                'precio_entrada',
                'link_entradas',
                'destacado',
                'imagen_destacada',
                'slug',
                'lat',
                'lng',
                'provincia_id'
            ]);
        });
    }
};
