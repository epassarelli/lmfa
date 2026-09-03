<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `radio_signals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `excerpt` VARCHAR(1000) NULL,
    `body` LONGTEXT NOT NULL,
    `editorial_focus` VARCHAR(30) NOT NULL DEFAULT 'folklore',
    `transmission_modes` JSON NOT NULL,
    `province_id` INT NULL,
    `locality_id` BIGINT UNSIGNED NULL,
    `city` VARCHAR(255) NULL,
    `address` VARCHAR(255) NULL,
    `latitude` DECIMAL(10,8) NULL,
    `longitude` DECIMAL(11,8) NULL,
    `coverage_scope` VARCHAR(30) NOT NULL DEFAULT 'local',
    `coverage_notes` TEXT NULL,
    `phone` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `website` VARCHAR(255) NULL,
    `source_urls` JSON NOT NULL,
    `verification_status` VARCHAR(30) NOT NULL DEFAULT 'pending',
    `last_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `verified_by_user_id` BIGINT UNSIGNED NULL,
    `verification_method` VARCHAR(50) NULL,
    `editorial_status` VARCHAR(30) NOT NULL DEFAULT 'draft',
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `featured_image_path` VARCHAR(255) NULL,
    `image_alt` VARCHAR(255) NULL,
    `seo_title` VARCHAR(255) NULL,
    `meta_description` VARCHAR(320) NULL,
    `visits` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `radio_signals_slug_unique` (`slug`),
    KEY `radio_signals_public_index` (`editorial_status`, `verification_status`, `published_at`),
    KEY `radio_signals_discovery_index` (`province_id`, `locality_id`, `editorial_focus`),
    KEY `radio_signals_verification_index` (`last_verified_at`),
    KEY `radio_signals_verified_by_user_id_foreign` (`verified_by_user_id`),
    KEY `radio_signals_created_by_foreign` (`created_by`),
    KEY `radio_signals_locality_id_foreign` (`locality_id`),
    CONSTRAINT `radio_signals_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provincias` (`id`) ON DELETE SET NULL,
    CONSTRAINT `radio_signals_locality_id_foreign` FOREIGN KEY (`locality_id`) REFERENCES `localities` (`id`) ON DELETE SET NULL,
    CONSTRAINT `radio_signals_verified_by_user_id_foreign` FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `radio_signals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('radio_signals');
    }
};
