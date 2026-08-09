<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('comidas', 'comidas_estado_titulo_index', ['estado', 'titulo']);
        $this->addIndexIfMissing('comidas', 'comidas_estado_visitas_index', ['estado', 'visitas']);
        $this->addIndexIfMissing('comidas', 'comidas_slug_index', ['slug']);

        $this->addIndexIfMissing('mitos', 'mitos_estado_titulo_index', ['estado', 'titulo']);
        $this->addIndexIfMissing('mitos', 'mitos_estado_visitas_index', ['estado', 'visitas']);
        $this->addIndexIfMissing('mitos', 'mitos_slug_index', ['slug']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('comidas', 'comidas_estado_titulo_index');
        $this->dropIndexIfExists('comidas', 'comidas_estado_visitas_index');
        $this->dropIndexIfExists('comidas', 'comidas_slug_index');

        $this->dropIndexIfExists('mitos', 'mitos_estado_titulo_index');
        $this->dropIndexIfExists('mitos', 'mitos_estado_visitas_index');
        $this->dropIndexIfExists('mitos', 'mitos_slug_index');
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
