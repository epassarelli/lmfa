<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `radio_listening_channels` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `radio_signal_id` BIGINT UNSIGNED NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    `channel_type` VARCHAR(30) NOT NULL,
    `platform` VARCHAR(30) NULL,
    `frequency_band` VARCHAR(10) NULL,
    `frequency` VARCHAR(50) NULL,
    `url` VARCHAR(2048) NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `radio_listening_channels_signal_active_index` (`radio_signal_id`, `is_active`, `sort_order`),
    CONSTRAINT `radio_listening_channels_radio_signal_id_foreign` FOREIGN KEY (`radio_signal_id`) REFERENCES `radio_signals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `radio_programs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `radio_signal_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `excerpt` VARCHAR(1000) NULL,
    `body` LONGTEXT NOT NULL,
    `is_folklore` TINYINT(1) NOT NULL DEFAULT 1,
    `platform` VARCHAR(30) NULL,
    `listening_url` VARCHAR(2048) NULL,
    `source_urls` JSON NOT NULL,
    `verification_status` VARCHAR(30) NOT NULL DEFAULT 'pending',
    `last_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `verified_by_user_id` BIGINT UNSIGNED NULL,
    `verification_method` VARCHAR(50) NULL,
    `editorial_status` VARCHAR(30) NOT NULL DEFAULT 'draft',
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `seo_title` VARCHAR(255) NULL,
    `meta_description` VARCHAR(320) NULL,
    `visits` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `radio_programs_slug_unique` (`slug`),
    KEY `radio_programs_public_index` (`editorial_status`, `verification_status`, `published_at`),
    KEY `radio_programs_signal_index` (`radio_signal_id`),
    KEY `radio_programs_verified_by_user_id_foreign` (`verified_by_user_id`),
    KEY `radio_programs_created_by_foreign` (`created_by`),
    CONSTRAINT `radio_programs_radio_signal_id_foreign` FOREIGN KEY (`radio_signal_id`) REFERENCES `radio_signals` (`id`) ON DELETE SET NULL,
    CONSTRAINT `radio_programs_verified_by_user_id_foreign` FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `radio_programs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `radio_program_slots` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `radio_program_id` BIGINT UNSIGNED NOT NULL,
    `weekday` TINYINT UNSIGNED NOT NULL,
    `starts_at` TIME NOT NULL,
    `ends_at` TIME NULL,
    `timezone` VARCHAR(64) NOT NULL DEFAULT 'America/Argentina/Buenos_Aires',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `radio_program_slots_unique` (`radio_program_id`, `weekday`, `starts_at`),
    KEY `radio_program_slots_schedule_index` (`weekday`, `starts_at`, `is_active`),
    CONSTRAINT `radio_program_slots_radio_program_id_foreign` FOREIGN KEY (`radio_program_id`) REFERENCES `radio_programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('radio_program_slots');
        Schema::dropIfExists('radio_programs');
        Schema::dropIfExists('radio_listening_channels');
    }
};
