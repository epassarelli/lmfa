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
        // 1. Rename table
        Schema::rename('noticias', 'news');

        // 2. Rename columns
        Schema::table('news', function (Blueprint $table) {
            $table->renameColumn('titulo', 'title');
            $table->renameColumn('noticia', 'body');
            $table->renameColumn('user_id', 'created_by');
            $table->renameColumn('publicar', 'published_at');
            $table->renameColumn('foto', 'featured_image_path');
        });

        // 3. Add new columns
        Schema::table('news', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')->constrained('organizations')->nullOnDelete();
            $table->string('subtitle')->nullable()->after('title');
            $table->text('excerpt')->nullable()->after('subtitle');
            $table->string('news_type', 50)->default('general')->after('body');
            $table->string('editorial_status', 30)->default('published')->after('estado'); // default to published for migrated data
            $table->string('publication_mode', 40)->default('portal_only')->after('editorial_status');
            $table->unsignedBigInteger('featured_image_id')->nullable()->after('publication_mode');
            $table->string('seo_title')->nullable()->after('featured_image_id');
            $table->text('meta_description')->nullable()->after('seo_title');
            $table->foreignId('approved_by')->nullable()->after('meta_description')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
        
        // 4. Update editorial_status for old news (if estado was 1)
        \DB::statement("UPDATE news SET editorial_status = 'published' WHERE estado = 1");
        \DB::statement("UPDATE news SET editorial_status = 'draft' WHERE estado = 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['approved_by']);
            
            $table->dropColumn([
                'organization_id',
                'subtitle',
                'excerpt',
                'news_type',
                'editorial_status',
                'publication_mode',
                'featured_image_id',
                'seo_title',
                'meta_description',
                'approved_by',
                'approved_at'
            ]);

            $table->renameColumn('title', 'titulo');
            $table->renameColumn('body', 'noticia');
            $table->renameColumn('created_by', 'user_id');
            $table->renameColumn('published_at', 'publicar');
            $table->renameColumn('featured_image_path', 'foto');
        });

        Schema::rename('news', 'noticias');
    }
};
