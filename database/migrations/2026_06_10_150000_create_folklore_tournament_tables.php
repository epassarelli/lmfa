<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folklore_tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('year');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('status')->default('draft')->index();
            $table->longText('rules')->nullable();
            $table->timestamps();

            $table->index('year');
        });

        Schema::create('folklore_tournament_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('folklore_tournaments')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('folklore_tournament_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('folklore_tournaments')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('folklore_tournament_groups')->nullOnDelete();
            $table->integer('artist_id')->nullable();
            $table->foreign('artist_id')->references('id')->on('interpretes')->nullOnDelete();
            $table->string('display_name');
            $table->string('slug')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('seed_order')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('folklore_tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('folklore_tournaments')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('folklore_tournament_groups')->nullOnDelete();
            $table->string('phase');
            $table->unsignedInteger('matchday')->nullable();
            $table->foreignId('participant_1_id')->constrained('folklore_tournament_participants')->cascadeOnDelete();
            $table->foreignId('participant_2_id')->constrained('folklore_tournament_participants')->cascadeOnDelete();
            $table->unsignedInteger('participant_1_votes')->default(0);
            $table->unsignedInteger('participant_2_votes')->default(0);
            $table->foreignId('winner_participant_id')->nullable()->constrained('folklore_tournament_participants')->nullOnDelete();
            $table->string('status')->default('scheduled');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('voting_opens_at')->nullable();
            $table->dateTime('voting_closes_at')->nullable();
            $table->string('instagram_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folklore_tournament_matches');
        Schema::dropIfExists('folklore_tournament_participants');
        Schema::dropIfExists('folklore_tournament_groups');
        Schema::dropIfExists('folklore_tournaments');
    }
};
