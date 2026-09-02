<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penia_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_penia_id')->nullable()->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 1000)->nullable();
            $table->longText('body');
            $table->unsignedInteger('province_id');
            $table->unsignedBigInteger('locality_id')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('venue_type', 50);
            $table->json('opening_hours')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('reservation_url')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->text('accessibility_notes')->nullable();
            $table->text('regular_events_summary')->nullable();
            $table->text('admission_notes')->nullable();
            $table->json('source_urls');
            $table->string('verification_status', 30)->default('pending');
            $table->timestamp('last_verified_at')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('verification_method', 50)->nullable();
            $table->string('editorial_status', 30)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('featured_image_id')->nullable();
            $table->string('featured_image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->unsignedInteger('visits')->default(0);
            $table->timestamps();

            $table->foreign('legacy_penia_id')->references('id')->on('penias')->nullOnDelete();
            $table->foreign('province_id')->references('id')->on('provincias')->restrictOnDelete();
            $table->foreign('locality_id')->references('id')->on('localities')->nullOnDelete();
            $table->index(['editorial_status', 'verification_status', 'published_at'], 'penia_profiles_public_index');
            $table->index(['province_id', 'locality_id', 'venue_type'], 'penia_profiles_discovery_index');
            $table->index('last_verified_at', 'penia_profiles_verification_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penia_profiles');
    }
};
