<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Mismo patron que 2026_08_09_140000_add_secondary_performance_indexes.php y
// 2026_08_09_150000_add_content_section_indexes.php: news/radios/penias
// quedaron afuera de esa pasada y su columna "slug" (usada en cada
// route-model-binding de noticia/radio/penia) sigue sin indice, es decir,
// full table scan en cada visita a esas paginas.
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('news', 'news_slug_index', ['slug']);
        $this->addIndexIfMissing('radios', 'radios_slug_index', ['slug']);
        $this->addIndexIfMissing('penias', 'penias_slug_index', ['slug']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('news', 'news_slug_index');
        $this->dropIndexIfExists('radios', 'radios_slug_index');
        $this->dropIndexIfExists('penias', 'penias_slug_index');
    }

    private function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        if (! Schema::hasTable($table) || $this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
            $tableBlueprint->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
            $tableBlueprint->dropIndex($indexName);
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
