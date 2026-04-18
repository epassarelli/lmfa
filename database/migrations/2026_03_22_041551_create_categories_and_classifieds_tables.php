<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories table (for classifieds, independent of 'categorias' for news)
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon')->nullable(); // emoji o clase de icono
                $table->timestamps();
            });

            // Seed default categories
            DB::table('categories')->insert([
                ['name' => 'Instrumentos',  'slug' => 'instrumentos',  'icon' => '🎸', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Indumentaria',  'slug' => 'indumentaria',  'icon' => '👗', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Clases',        'slug' => 'clases',        'icon' => '👨‍🏫', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Artistas',      'slug' => 'artistas',      'icon' => '🎤', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Servicios',     'slug' => 'servicios',     'icon' => '🔧', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Eventos',       'slug' => 'eventos',       'icon' => '📅', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Peñas',         'slug' => 'penias',        'icon' => '🏠', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Otros',         'slug' => 'otros',         'icon' => '📦', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Tags table
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        // Main classifieds table
        if (!Schema::hasTable('classifieds')) {
            Schema::create('classifieds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('category_id')->constrained();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description');
                $table->string('price')->nullable();
                $table->string('location')->nullable();
                $table->string('contact_info')->nullable();
                $table->string('contact_whatsapp')->nullable();
                $table->date('expiration_date')->nullable();
                $table->boolean('is_active')->default(false); // requires admin approval
                $table->boolean('is_featured')->default(false); // destacado
                $table->string('estado')->default('pendiente'); // pendiente | activo | rechazado | vencido
                $table->text('moderator_comment')->nullable();
                $table->timestamps();
            });
        }

        // Pivot: classified_tag
        if (!Schema::hasTable('classified_tag')) {
            Schema::create('classified_tag', function (Blueprint $table) {
                $table->foreignId('classified_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
                $table->primary(['classified_id', 'tag_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('classified_tag');
        Schema::dropIfExists('classifieds');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
    }
};
