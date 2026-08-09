<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('news', 'news_published_at_index', ['published_at']);
        $this->addIndexIfMissing('news', 'news_created_by_published_at_index', ['created_by', 'published_at']);
        $this->addIndexIfMissing('news', 'news_categoria_id_published_at_index', ['categoria_id', 'published_at']);

        $this->addIndexIfMissing('events', 'events_start_at_index', ['start_at']);
        $this->addIndexIfMissing('events', 'events_editorial_status_start_at_index', ['editorial_status', 'start_at']);
        $this->addIndexIfMissing('events', 'events_province_id_start_at_index', ['province_id', 'start_at']);

        $this->addIndexIfMissing('festivales', 'festivales_status_published_at_index', ['status', 'published_at']);
        $this->addIndexIfMissing('festivales', 'festivales_province_id_mes_id_index', ['province_id', 'mes_id']);
        $this->addIndexIfMissing('festivales', 'festivales_title_index', ['title']);
        $this->addIndexIfMissing('festivales', 'festivales_user_id_index', ['user_id']);

        $this->addIndexIfMissing('interpretes', 'interpretes_interprete_index', ['interprete']);
        $this->addIndexIfMissing('interpretes', 'interpretes_estado_interprete_index', ['estado', 'interprete']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('news', 'news_published_at_index');
        $this->dropIndexIfExists('news', 'news_created_by_published_at_index');
        $this->dropIndexIfExists('news', 'news_categoria_id_published_at_index');

        $this->dropIndexIfExists('events', 'events_start_at_index');
        $this->dropIndexIfExists('events', 'events_editorial_status_start_at_index');
        $this->dropIndexIfExists('events', 'events_province_id_start_at_index');

        $this->dropIndexIfExists('festivales', 'festivales_status_published_at_index');
        $this->dropIndexIfExists('festivales', 'festivales_province_id_mes_id_index');
        $this->dropIndexIfExists('festivales', 'festivales_title_index');
        $this->dropIndexIfExists('festivales', 'festivales_user_id_index');

        $this->dropIndexIfExists('interpretes', 'interpretes_interprete_index');
        $this->dropIndexIfExists('interpretes', 'interpretes_estado_interprete_index');
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
