<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penia_profile_event', function (Blueprint $table) {
            $table->unsignedBigInteger('penia_profile_id');
            $table->unsignedBigInteger('event_id');
            $table->timestamps();

            $table->primary(['penia_profile_id', 'event_id']);
            $table->foreign('penia_profile_id')->references('id')->on('penia_profiles')->cascadeOnDelete();
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penia_profile_event');
    }
};
