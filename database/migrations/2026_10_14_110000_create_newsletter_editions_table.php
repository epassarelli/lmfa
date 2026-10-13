<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_editions', function (Blueprint $table) {
            $table->id();
            $table->string('edition_key', 64)->unique();
            $table->string('subject');
            $table->text('intro')->nullable();
            $table->enum('status', ['draft', 'approved', 'sent'])->default('draft');
            $table->date('period_start');
            $table->date('period_end');
            $table->json('selection')->nullable();   // ids elegidos por tipo (news, events, albums, songs, artists)
            $table->json('snapshot')->nullable();    // copia congelada del contenido al aprobar
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('test_sent_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_editions');
    }
};
