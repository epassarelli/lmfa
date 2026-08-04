<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->recreatePivot(
            'knowledge_article_interprete',
            'interprete_id',
            'INT NOT NULL',
            'interpretes',
            'knowledge_article_interprete_interprete_id_foreign'
        );

        $this->recreatePivot(
            'knowledge_article_cancion',
            'cancion_id',
            'BIGINT UNSIGNED NOT NULL',
            'canciones',
            'knowledge_article_cancion_cancion_id_foreign'
        );

        $this->recreatePivot(
            'knowledge_article_album',
            'album_id',
            'INT NOT NULL',
            'albunes',
            'knowledge_article_album_album_id_foreign'
        );

        $this->recreatePivot(
            'knowledge_article_festival',
            'festival_id',
            'INT NOT NULL',
            'festivales',
            'knowledge_article_festival_festival_id_foreign'
        );

        $this->recreatePivot(
            'event_knowledge_article',
            'event_id',
            'BIGINT UNSIGNED NOT NULL',
            'events',
            'event_knowledge_article_event_id_foreign'
        );

        $this->recreatePivot(
            'knowledge_article_provincia',
            'provincia_id',
            'INT NOT NULL',
            'provincias',
            'knowledge_article_provincia_provincia_id_foreign'
        );

        $this->dropTableIfExists('knowledge_article_related');

        DB::unprepared(<<<'SQL'
CREATE TABLE `knowledge_article_related` (
    `knowledge_article_id` BIGINT UNSIGNED NOT NULL,
    `related_knowledge_article_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`knowledge_article_id`, `related_knowledge_article_id`),
    KEY `knowledge_article_related_related_knowledge_article_id_index` (`related_knowledge_article_id`),
    CONSTRAINT `knowledge_article_related_knowledge_article_id_foreign`
        FOREIGN KEY (`knowledge_article_id`) REFERENCES `knowledge_articles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `knowledge_article_related_related_knowledge_article_id_foreign`
        FOREIGN KEY (`related_knowledge_article_id`) REFERENCES `knowledge_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_article_related');
        Schema::dropIfExists('knowledge_article_provincia');
        Schema::dropIfExists('event_knowledge_article');
        Schema::dropIfExists('knowledge_article_festival');
        Schema::dropIfExists('knowledge_article_album');
        Schema::dropIfExists('knowledge_article_cancion');
        Schema::dropIfExists('knowledge_article_interprete');
    }

    private function recreatePivot(
        string $tableName,
        string $relatedColumn,
        string $relatedColumnDefinition,
        string $relatedTable,
        string $foreignKeyName
    ): void {
        $this->dropTableIfExists($tableName);

        DB::unprepared(
            sprintf(
                <<<'SQL'
CREATE TABLE `%s` (
    `knowledge_article_id` BIGINT UNSIGNED NOT NULL,
    `%s` %s,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`knowledge_article_id`, `%s`),
    KEY `%s_%s_index` (`%s`),
    CONSTRAINT `%s`
        FOREIGN KEY (`knowledge_article_id`) REFERENCES `knowledge_articles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `%s`
        FOREIGN KEY (`%s`) REFERENCES `%s` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
                $tableName,
                $relatedColumn,
                $relatedColumnDefinition,
                $relatedColumn,
                $tableName,
                $relatedColumn,
                $relatedColumn,
                $tableName.'_knowledge_article_id_foreign',
                $foreignKeyName,
                $relatedColumn,
                $relatedTable
            )
        );
    }

    private function dropTableIfExists(string $tableName): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists($tableName);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
