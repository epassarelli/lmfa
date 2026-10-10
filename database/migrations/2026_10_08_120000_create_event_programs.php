<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->unsignedBigInteger('venue_id')->nullable()->index();
            $table->string('ticket_url')->nullable();
            $table->string('price_text', 100)->nullable();
            $table->boolean('is_free')->default(false);
            $table->string('editorial_status', 30)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->index();
            $table->timestamps();
        });
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->constrained('event_programs')->restrictOnDelete();
            $table->json('program_overrides')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('program_id');
            $table->dropColumn('program_overrides');
        });
        Schema::dropIfExists('event_programs');
    }
};
