<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla media_assets de la pasarela de contenidos.
     * La tabla 'images' existente se mantiene intacta para compatibilidad.
     */
    public function up(): void
    {
        if (Schema::hasTable('media_assets')) {
            // Agregar columnas faltantes si la tabla ya existe
            Schema::table('media_assets', function (Blueprint $table) {
                if (!Schema::hasColumn('media_assets', 'disk')) {
                    $table->string('disk')->default('public');
                }
                if (!Schema::hasColumn('media_assets', 'original_name')) {
                    $table->string('original_name')->nullable();
                }
                if (!Schema::hasColumn('media_assets', 'size')) {
                    $table->unsignedBigInteger('size')->nullable();
                }
                if (!Schema::hasColumn('media_assets', 'caption')) {
                    $table->string('caption')->nullable();
                }
                if (!Schema::hasColumn('media_assets', 'group')) {
                    $table->string('group', 50)->nullable();
                }
                if (!Schema::hasColumn('media_assets', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
            });
            return;
        }

        // Crear tabla media_assets desde cero
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('imageable_type')->nullable();
            $table->unsignedBigInteger('imageable_id')->nullable();
            $table->string('profile')->nullable();
            $table->string('original_path');
            $table->json('variants_json')->nullable();
            $table->string('alt')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('original_width')->nullable();
            $table->integer('original_height')->nullable();
            $table->string('mime')->nullable();
            $table->string('disk')->default('public');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('caption')->nullable();
            $table->string('group', 50)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['imageable_type', 'imageable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
