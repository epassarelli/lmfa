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
        // Ya existe la tabla minimal de un intento previo o del test_create.php
        
        Schema::table('events', function (Blueprint $table) {
            // Campos que faltan (asumiendo que los de 'test_create.php' están ahí)
            // test_create puso: id, title, slug, start_at, status, timestamps
            
            $table->foreignId('organization_id')->nullable()->after('id');
            $table->foreignId('venue_id')->nullable()->after('organization_id');
            $table->string('subtitle')->nullable()->after('title');
            $table->text('excerpt')->nullable()->after('subtitle');
            $table->longText('body')->nullable()->after('excerpt');
            $table->string('event_type', 50)->default('recital')->after('body');
            $table->string('modality', 30)->default('presencial')->after('event_type');
            $table->timestamp('end_at')->nullable()->after('start_at');
            $table->string('timezone', 50)->default('America/Argentina/Buenos_Aires')->after('end_at');
            $table->unsignedBigInteger('province_id')->nullable()->after('timezone');
            $table->string('city')->nullable()->after('province_id');
            $table->string('address')->nullable()->after('city');
            $table->decimal('latitude', 10, 8)->nullable()->after('address');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('ticket_url')->nullable()->after('longitude');
            $table->string('price_text')->nullable()->after('ticket_url');
            $table->boolean('is_free')->default(false)->after('price_text');
            $table->integer('capacity')->nullable()->after('is_free');
            $table->string('editorial_status', 30)->default('draft')->after('status');
            $table->string('publication_mode', 40)->default('portal_only')->after('editorial_status');
            $table->unsignedBigInteger('featured_image_id')->nullable()->after('publication_mode');
            $table->string('featured_image_path')->nullable()->after('featured_image_id');
            $table->string('seo_title')->nullable()->after('featured_image_path');
            $table->text('meta_description')->nullable()->after('seo_title');
            $table->foreignId('approved_by')->nullable()->after('meta_description');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('published_at')->nullable()->after('approved_at');
            $table->foreignId('created_by')->nullable()->after('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
