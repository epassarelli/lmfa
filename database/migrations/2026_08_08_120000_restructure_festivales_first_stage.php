<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('localities')) {
            Schema::create('localities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('province_id')->constrained('provincias')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->timestamps();
                $table->unique(['province_id', 'slug']);
            });
        }

        Schema::table('festivales', function (Blueprint $table) {
            if (! Schema::hasColumn('festivales', 'title')) {
                $table->string('title', 255)->nullable()->after('slug');
            }
            if (! Schema::hasColumn('festivales', 'excerpt')) {
                $table->text('excerpt')->nullable()->after('title');
            }
            if (! Schema::hasColumn('festivales', 'body')) {
                $table->longText('body')->nullable()->after('excerpt');
            }
            if (! Schema::hasColumn('festivales', 'featured_image_id')) {
                $table->unsignedBigInteger('featured_image_id')->nullable()->after('body');
            }
            if (! Schema::hasColumn('festivales', 'featured_image_path')) {
                $table->string('featured_image_path', 255)->nullable()->after('featured_image_id');
            }
            if (! Schema::hasColumn('festivales', 'seo_title')) {
                $table->string('seo_title', 255)->nullable()->after('featured_image_path');
            }
            if (! Schema::hasColumn('festivales', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('seo_title');
            }
            if (! Schema::hasColumn('festivales', 'status')) {
                $table->string('status', 30)->nullable()->after('meta_description');
            }
            if (! Schema::hasColumn('festivales', 'published_at')) {
                $table->dateTime('published_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('festivales', 'province_id')) {
                $table->unsignedInteger('province_id')->nullable()->after('featured_image_path');
            }
            if (! Schema::hasColumn('festivales', 'locality_id')) {
                $table->foreignId('locality_id')->nullable()->after('province_id')->constrained('localities')->nullOnDelete();
            }
        });

        DB::table('festivales')
            ->whereNull('title')
            ->update(['title' => DB::raw('titulo')]);

        DB::table('festivales')
            ->whereNull('body')
            ->update(['body' => DB::raw('detalle')]);

        DB::table('festivales')
            ->whereNull('featured_image_path')
            ->update(['featured_image_path' => DB::raw('foto')]);

        DB::table('festivales')
            ->whereNull('published_at')
            ->update(['published_at' => DB::raw('publicar')]);

        DB::table('festivales')
            ->whereNull('status')
            ->update(['status' => DB::raw("CASE WHEN estado = 1 THEN 'published' ELSE 'draft' END")]);

        DB::table('festivales')
            ->whereNull('province_id')
            ->update(['province_id' => DB::raw('provincia_id')]);

        $this->createPivotTable(
            'festival_news',
            'festival_id',
            'INT NOT NULL',
            'festivales',
            'news_id',
            'BIGINT UNSIGNED NOT NULL',
            'news'
        );

        $this->createPivotTable(
            'event_festival',
            'festival_id',
            'INT NOT NULL',
            'festivales',
            'event_id',
            'BIGINT UNSIGNED NOT NULL',
            'events'
        );

        $this->createPivotTable(
            'festival_interprete',
            'festival_id',
            'INT NOT NULL',
            'festivales',
            'interprete_id',
            'INT NOT NULL',
            'interpretes'
        );

        Schema::table('festivales', function (Blueprint $table) {
            if (Schema::hasColumn('festivales', 'titulo')) {
                $table->dropColumn('titulo');
            }
            if (Schema::hasColumn('festivales', 'detalle')) {
                $table->dropColumn('detalle');
            }
            if (Schema::hasColumn('festivales', 'foto')) {
                $table->dropColumn('foto');
            }
            if (Schema::hasColumn('festivales', 'publicar')) {
                $table->dropColumn('publicar');
            }
            if (Schema::hasColumn('festivales', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('festivales', 'provincia_id')) {
                $table->dropColumn('provincia_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('festivales', function (Blueprint $table) {
            if (! Schema::hasColumn('festivales', 'titulo')) {
                $table->string('titulo')->nullable()->after('id');
            }
            if (! Schema::hasColumn('festivales', 'detalle')) {
                $table->longText('detalle')->nullable()->after('slug');
            }
            if (! Schema::hasColumn('festivales', 'foto')) {
                $table->string('foto')->nullable()->after('detalle');
            }
            if (! Schema::hasColumn('festivales', 'provincia_id')) {
                $table->unsignedInteger('provincia_id')->nullable()->after('foto');
            }
            if (! Schema::hasColumn('festivales', 'publicar')) {
                $table->dateTime('publicar')->nullable()->after('visitas');
            }
            if (! Schema::hasColumn('festivales', 'estado')) {
                $table->integer('estado')->default(0)->after('publicar');
            }
        });

        DB::table('festivales')
            ->whereNull('titulo')
            ->update(['titulo' => DB::raw('title')]);

        DB::table('festivales')
            ->whereNull('detalle')
            ->update(['detalle' => DB::raw('body')]);

        DB::table('festivales')
            ->whereNull('foto')
            ->update(['foto' => DB::raw('featured_image_path')]);

        DB::table('festivales')
            ->whereNull('publicar')
            ->update(['publicar' => DB::raw('published_at')]);

        DB::table('festivales')
            ->whereNull('estado')
            ->update(['estado' => DB::raw("CASE WHEN status = 'published' THEN 1 ELSE 0 END")]);

        DB::table('festivales')
            ->whereNull('provincia_id')
            ->update(['provincia_id' => DB::raw('province_id')]);

        Schema::dropIfExists('festival_interprete');
        Schema::dropIfExists('event_festival');
        Schema::dropIfExists('festival_news');

        Schema::table('festivales', function (Blueprint $table) {
            if (Schema::hasColumn('festivales', 'locality_id')) {
                $table->dropConstrainedForeignId('locality_id');
            }

            $dropColumns = collect([
                'title',
                'excerpt',
                'body',
                'featured_image_id',
                'featured_image_path',
                'seo_title',
                'meta_description',
                'status',
                'published_at',
                'province_id',
            ])->filter(fn (string $column) => Schema::hasColumn('festivales', $column))->all();

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });

        Schema::dropIfExists('localities');
    }

    private function createPivotTable(
        string $tableName,
        string $leftColumn,
        string $leftDefinition,
        string $leftTable,
        string $rightColumn,
        string $rightDefinition,
        string $rightTable
    ): void {
        if (Schema::hasTable($tableName)) {
            return;
        }

        DB::unprepared(sprintf(
            <<<'SQL'
CREATE TABLE `%s` (
    `%s` %s,
    `%s` %s,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `%s_unique` (`%s`, `%s`),
    KEY `%s_%s_index` (`%s`),
    CONSTRAINT `%s_%s_foreign`
        FOREIGN KEY (`%s`) REFERENCES `%s` (`id`) ON DELETE CASCADE,
    CONSTRAINT `%s_%s_foreign`
        FOREIGN KEY (`%s`) REFERENCES `%s` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            $tableName,
            $leftColumn,
            $leftDefinition,
            $rightColumn,
            $rightDefinition,
            $tableName,
            $leftColumn,
            $rightColumn,
            $tableName,
            $rightColumn,
            $rightColumn,
            $tableName,
            $leftColumn,
            $leftColumn,
            $leftTable,
            $tableName,
            $rightColumn,
            $rightColumn,
            $rightTable
        ));
    }
};
