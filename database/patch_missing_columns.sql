-- ============================================================
-- PATCH: columnas faltantes detectadas al comparar migraciones
--        con el esquema actual creado via setup_pasarela_tables.sql
-- Compatible con MariaDB 10.8+ (soporta ADD COLUMN IF NOT EXISTS)
-- ============================================================

-- ----------------------------------------------------------------
-- 0. newsletter_subscribers: tabla faltante en setup_pasarela_tables.sql
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint unsigned DEFAULT NULL,
  email varchar(255) NOT NULL,
  name varchar(255) DEFAULT NULL,
  status enum('active','unsubscribed','bounced') NOT NULL DEFAULT 'active',
  token varchar(64) NOT NULL,
  source varchar(255) DEFAULT NULL,
  unsubscribed_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY newsletter_subscribers_email_unique (email),
  UNIQUE KEY newsletter_subscribers_token_unique (token),
  KEY newsletter_subscribers_user_id_foreign (user_id),
  CONSTRAINT newsletter_subscribers_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

-- ----------------------------------------------------------------
-- 1. users: campos de publicador (migración 2026_04_09_031600)
-- ----------------------------------------------------------------
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS google_id varchar(255) DEFAULT NULL AFTER email,
  ADD COLUMN IF NOT EXISTS facebook_id varchar(255) DEFAULT NULL AFTER google_id,
  ADD COLUMN IF NOT EXISTS phone varchar(255) DEFAULT NULL AFTER rank,
  ADD COLUMN IF NOT EXISTS status varchar(30) NOT NULL DEFAULT 'active' AFTER phone,
  ADD COLUMN IF NOT EXISTS is_verified_publisher tinyint(1) NOT NULL DEFAULT 0 AFTER status,
  ADD COLUMN IF NOT EXISTS publisher_type_default varchar(50) DEFAULT NULL AFTER is_verified_publisher,
  ADD COLUMN IF NOT EXISTS last_login_at timestamp NULL DEFAULT NULL AFTER publisher_type_default;

-- ----------------------------------------------------------------
-- 2. organizations: campos del modelo completo (migración 2026_04_09_032400)
-- ----------------------------------------------------------------
ALTER TABLE organizations
  ADD COLUMN IF NOT EXISTS legal_name varchar(255) DEFAULT NULL AFTER slug,
  ADD COLUMN IF NOT EXISTS bio_short text DEFAULT NULL AFTER legal_name,
  ADD COLUMN IF NOT EXISTS bio_long longtext DEFAULT NULL AFTER bio_short,
  ADD COLUMN IF NOT EXISTS logo_media_id bigint unsigned DEFAULT NULL AFTER banner_path,
  ADD COLUMN IF NOT EXISTS cover_media_id bigint unsigned DEFAULT NULL AFTER logo_media_id,
  ADD COLUMN IF NOT EXISTS is_verified tinyint(1) NOT NULL DEFAULT 0 AFTER status;

-- ----------------------------------------------------------------
-- 3. social_accounts: refresh token (migración 2026_04_09_045500)
-- ----------------------------------------------------------------
ALTER TABLE social_accounts
  ADD COLUMN IF NOT EXISTS refresh_token_encrypted text DEFAULT NULL AFTER token_encrypted;

-- ----------------------------------------------------------------
-- 4. venues: campos descriptivos (migración 2026_04_09_033230)
-- ----------------------------------------------------------------
ALTER TABLE venues
  ADD COLUMN IF NOT EXISTS description text DEFAULT NULL AFTER slug,
  ADD COLUMN IF NOT EXISTS phone varchar(255) DEFAULT NULL AFTER capacity,
  ADD COLUMN IF NOT EXISTS website varchar(255) DEFAULT NULL AFTER phone,
  ADD COLUMN IF NOT EXISTS status varchar(30) NOT NULL DEFAULT 'active' AFTER website;

-- ----------------------------------------------------------------
-- Asegurar migraciones pendientes marcadas como ejecutadas
-- ----------------------------------------------------------------
INSERT IGNORE INTO migrations (migration, batch) VALUES
  ('2026_03_22_041551_create_categories_and_classifieds_tables', 2),
  ('2026_03_23_024951_create_newsletter_subscribers_table', 2),
  ('2026_04_09_031600_add_publisher_fields_to_users_table', 3),
  ('2026_04_09_032400_create_organizations_table', 3),
  ('2026_04_09_032700_create_organization_members_table', 3),
  ('2026_04_09_033200_transform_shows_to_events_table', 3),
  ('2026_04_09_033230_create_venues_table', 3),
  ('2026_04_09_035300_transform_noticias_to_news_table', 3),
  ('2026_04_09_035700_transform_images_to_media_assets_table', 3),
  ('2026_04_09_040200_make_profile_nullable_in_media_assets', 3),
  ('2026_04_09_042000_create_event_interprete_table', 3),
  ('2026_04_09_042900_create_moderation_reviews_table', 3),
  ('2026_04_09_045500_create_social_accounts_table', 3),
  ('2026_04_09_050000_create_publication_orchestration_tables', 3),
  ('2026_04_09_053000_create_templates_notifications_audit_tables', 3),
  ('2026_04_09_212730_create_jobs_table', 3);
