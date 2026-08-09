<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('interpretes', 'interpretes_slug_index', ['slug']);

        $this->addIndexIfMissing('albunes', 'albunes_estado_created_at_index', ['estado', 'created_at']);
        $this->addIndexIfMissing('albunes', 'albunes_interprete_id_anio_index', ['interprete_id', 'anio']);
        $this->addIndexIfMissing('albunes', 'albunes_slug_index', ['slug']);

        $this->addIndexIfMissing('canciones', 'canciones_estado_cancion_index', ['estado', 'cancion']);
        $this->addIndexIfMissing('canciones', 'canciones_interprete_id_cancion_index', ['interprete_id', 'cancion']);
        $this->addIndexIfMissing('canciones', 'canciones_slug_index', ['slug']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('interpretes', 'interpretes_slug_index');

        $this->dropIndexIfExists('albunes', 'albunes_estado_created_at_index');
        $this->dropIndexIfExists('albunes', 'albunes_interprete_id_anio_index');
        $this->dropIndexIfExists('albunes', 'albunes_slug_index');

        $this->dropIndexIfExists('canciones', 'canciones_estado_cancion_index');
        $this->dropIndexIfExists('canciones', 'canciones_interprete_id_cancion_index');
        $this->dropIndexIfExists('canciones', 'canciones_slug_index');
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
