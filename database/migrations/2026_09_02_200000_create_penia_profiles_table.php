<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `penia_profiles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `legacy_penia_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `excerpt` VARCHAR(1000) NULL,
    `body` LONGTEXT NOT NULL,
    `province_id` INT NOT NULL,
    `locality_id` BIGINT UNSIGNED NULL,
    `city` VARCHAR(255) NULL,
    `address` VARCHAR(255) NULL,
    `latitude` DECIMAL(10,8) NULL,
    `longitude` DECIMAL(11,8) NULL,
    `venue_type` VARCHAR(50) NOT NULL,
    `opening_hours` JSON NULL,
    `phone` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `website` VARCHAR(255) NULL,
    `reservation_url` VARCHAR(255) NULL,
    `capacity` INT UNSIGNED NULL,
    `accessibility_notes` TEXT NULL,
    `regular_events_summary` TEXT NULL,
    `admission_notes` TEXT NULL,
    `source_urls` JSON NOT NULL,
    `verification_status` VARCHAR(30) NOT NULL DEFAULT 'pending',
    `last_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `verified_by_user_id` BIGINT UNSIGNED NULL,
    `verification_method` VARCHAR(50) NULL,
    `editorial_status` VARCHAR(30) NOT NULL DEFAULT 'draft',
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `featured_image_id` BIGINT UNSIGNED NULL,
    `featured_image_path` VARCHAR(255) NULL,
    `image_alt` VARCHAR(255) NULL,
    `seo_title` VARCHAR(255) NULL,
    `meta_description` VARCHAR(320) NULL,
    `visits` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `penia_profiles_legacy_penia_id_unique` (`legacy_penia_id`),
    UNIQUE KEY `penia_profiles_slug_unique` (`slug`),
    KEY `penia_profiles_verified_by_user_id_foreign` (`verified_by_user_id`),
    KEY `penia_profiles_created_by_foreign` (`created_by`),
    KEY `penia_profiles_locality_id_foreign` (`locality_id`),
    KEY `penia_profiles_public_index` (`editorial_status`, `verification_status`, `published_at`),
    KEY `penia_profiles_discovery_index` (`province_id`, `locality_id`, `venue_type`),
    KEY `penia_profiles_verification_index` (`last_verified_at`),
    CONSTRAINT `penia_profiles_legacy_penia_id_foreign`
        FOREIGN KEY (`legacy_penia_id`) REFERENCES `penias` (`id`) ON DELETE SET NULL,
    CONSTRAINT `penia_profiles_province_id_foreign`
        FOREIGN KEY (`province_id`) REFERENCES `provincias` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `penia_profiles_locality_id_foreign`
        FOREIGN KEY (`locality_id`) REFERENCES `localities` (`id`) ON DELETE SET NULL,
    CONSTRAINT `penia_profiles_verified_by_user_id_foreign`
        FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `penia_profiles_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('penia_profiles');
    }
};
