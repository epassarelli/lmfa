<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla events de la pasarela de contenidos.
     * La tabla 'shows' existente se mantiene intacta para compatibilidad.
     */
    public function up(): void
    {
        if (Schema::hasTable('events')) {
            // Si ya existe (con columnas mínimas), agregar las que falten
            Schema::table('events', function (Blueprint $table) {
                if (!Schema::hasColumn('events', 'organization_id')) {
                    $table->foreignId('organization_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('events', 'venue_id')) {
                    $table->foreignId('venue_id')->nullable();
                }
                if (!Schema::hasColumn('events', 'subtitle')) {
                    $table->string('subtitle')->nullable();
                }
                if (!Schema::hasColumn('events', 'excerpt')) {
                    $table->text('excerpt')->nullable();
                }
                if (!Schema::hasColumn('events', 'body')) {
                    $table->longText('body')->nullable();
                }
                if (!Schema::hasColumn('events', 'event_type')) {
                    $table->string('event_type', 50)->default('recital');
                }
                if (!Schema::hasColumn('events', 'modality')) {
                    $table->string('modality', 30)->default('presencial');
                }
                if (!Schema::hasColumn('events', 'slug')) {
                    $table->string('slug')->nullable();
                }
                if (!Schema::hasColumn('events', 'start_at')) {
                    $table->timestamp('start_at')->nullable();
                }
                if (!Schema::hasColumn('events', 'end_at')) {
                    $table->timestamp('end_at')->nullable();
                }
                if (!Schema::hasColumn('events', 'timezone')) {
                    $table->string('timezone', 50)->default('America/Argentina/Buenos_Aires');
                }
                if (!Schema::hasColumn('events', 'province_id')) {
                    $table->unsignedBigInteger('province_id')->nullable();
                }
                if (!Schema::hasColumn('events', 'city')) {
                    $table->string('city')->nullable();
                }
                if (!Schema::hasColumn('events', 'address')) {
                    $table->string('address')->nullable();
                }
                if (!Schema::hasColumn('events', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable();
                }
                if (!Schema::hasColumn('events', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable();
                }
                if (!Schema::hasColumn('events', 'ticket_url')) {
                    $table->string('ticket_url')->nullable();
                }
                if (!Schema::hasColumn('events', 'price_text')) {
                    $table->string('price_text')->nullable();
                }
                if (!Schema::hasColumn('events', 'is_free')) {
                    $table->boolean('is_free')->default(false);
                }
                if (!Schema::hasColumn('events', 'capacity')) {
                    $table->integer('capacity')->nullable();
                }
                if (!Schema::hasColumn('events', 'status')) {
                    $table->string('status', 30)->default('active');
                }
                if (!Schema::hasColumn('events', 'editorial_status')) {
                    $table->string('editorial_status', 30)->default('draft');
                }
                if (!Schema::hasColumn('events', 'publication_mode')) {
                    $table->string('publication_mode', 40)->default('portal_only');
                }
                if (!Schema::hasColumn('events', 'featured_image_id')) {
                    $table->unsignedBigInteger('featured_image_id')->nullable();
                }
                if (!Schema::hasColumn('events', 'featured_image_path')) {
                    $table->string('featured_image_path')->nullable();
                }
                if (!Schema::hasColumn('events', 'seo_title')) {
                    $table->string('seo_title')->nullable();
                }
                if (!Schema::hasColumn('events', 'meta_description')) {
                    $table->text('meta_description')->nullable();
                }
                if (!Schema::hasColumn('events', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable();
                }
                if (!Schema::hasColumn('events', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable();
                }
                if (!Schema::hasColumn('events', 'published_at')) {
                    $table->timestamp('published_at')->nullable();
                }
                if (!Schema::hasColumn('events', 'created_by')) {
                    $table->foreignId('created_by')->nullable();
                }
            });
            return;
        }

        // Crear tabla events desde cero
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('venue_id')->nullable();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('event_type', 50)->default('recital');
            $table->string('modality', 30)->default('presencial');
            $table->string('slug')->nullable()->unique();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('timezone', 50)->default('America/Argentina/Buenos_Aires');
            $table->unsignedBigInteger('province_id')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('ticket_url')->nullable();
            $table->string('price_text')->nullable();
            $table->boolean('is_free')->default(false);
            $table->integer('capacity')->nullable();
            $table->string('status', 30)->default('active');
            $table->string('editorial_status', 30)->default('draft');
            $table->string('publication_mode', 40)->default('portal_only');
            $table->unsignedBigInteger('featured_image_id')->nullable();
            $table->string('featured_image_path')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
