<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('knowledge_articles')) {
            return;
        }

        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_category_id')->constrained('knowledge_categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->unsignedBigInteger('featured_image_id')->nullable();
            $table->string('featured_image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('primary_keyword')->nullable();
            $table->text('secondary_keywords')->nullable();
            $table->string('editorial_status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('visits')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['knowledge_category_id', 'slug'], 'knowledge_articles_category_slug_unique');
            $table->index('editorial_status');
            $table->index('published_at');
            $table->index('last_verified_at');
            $table->index('author_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
    }
};
