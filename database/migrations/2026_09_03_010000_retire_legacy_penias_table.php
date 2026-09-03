<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('ALTER TABLE `penia_profiles` DROP FOREIGN KEY `penia_profiles_legacy_penia_id_foreign`');
        DB::unprepared('ALTER TABLE `penia_profiles` DROP INDEX `penia_profiles_legacy_penia_id_unique`');
        DB::unprepared('ALTER TABLE `penia_profiles` DROP COLUMN `legacy_penia_id`');
        DB::unprepared('DROP TABLE IF EXISTS `penias`');
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `penias` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titulo` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `detalle` TEXT NOT NULL,
    `foto` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `visitas` INT NOT NULL,
    `publicar` DATETIME NULL,
    `estado` INT NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `penias_user_id_foreign` (`user_id`),
    CONSTRAINT `penias_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared('ALTER TABLE `penia_profiles` ADD COLUMN `legacy_penia_id` BIGINT UNSIGNED NULL AFTER `id`');
        DB::unprepared('ALTER TABLE `penia_profiles` ADD UNIQUE KEY `penia_profiles_legacy_penia_id_unique` (`legacy_penia_id`)');
        DB::unprepared('ALTER TABLE `penia_profiles` ADD CONSTRAINT `penia_profiles_legacy_penia_id_foreign` FOREIGN KEY (`legacy_penia_id`) REFERENCES `penias` (`id`) ON DELETE SET NULL');
    }
};
