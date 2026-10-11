<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (! Schema::hasColumn('organizations', 'instagram')) {
                $table->string('instagram')->nullable()->after('website');
            }
            if (! Schema::hasColumn('organizations', 'facebook')) {
                $table->string('facebook')->nullable()->after('instagram');
            }
            if (! Schema::hasColumn('organizations', 'contact_stage')) {
                $table->string('contact_stage', 30)->default('prospect')->after('source')->index();
            }
            if (! Schema::hasColumn('organizations', 'last_contacted_at')) {
                $table->timestamp('last_contacted_at')->nullable()->after('contact_stage');
            }
            if (! Schema::hasColumn('organizations', 'contact_notes')) {
                $table->text('contact_notes')->nullable()->after('last_contacted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'contact_stage')) {
                $table->dropIndex(['contact_stage']);
            }

            $columns = array_filter(
                ['instagram', 'facebook', 'contact_stage', 'last_contacted_at', 'contact_notes'],
                fn (string $column) => Schema::hasColumn('organizations', $column)
            );

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
