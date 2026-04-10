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
        // Nota: renames ya se ejecutaron.
        
        Schema::table('media_assets', function (Blueprint $table) {
            if (!Schema::hasColumn('media_assets', 'disk')) {
                $table->string('disk')->default('public');
            }
            if (!Schema::hasColumn('media_assets', 'original_name')) {
                $table->string('original_name')->nullable();
            }
            if (!Schema::hasColumn('media_assets', 'size')) {
                $table->unsignedBigInteger('size')->nullable();
            }
            if (!Schema::hasColumn('media_assets', 'caption')) {
                $table->string('caption')->nullable();
            }
            if (!Schema::hasColumn('media_assets', 'group')) {
                $table->string('group', 50)->nullable();
            }
            if (!Schema::hasColumn('media_assets', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ...
    }
};
