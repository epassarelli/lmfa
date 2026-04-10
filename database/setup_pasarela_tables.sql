SET FOREIGN_KEY_CHECKS=0;

-- ============================================================
-- TABLAS BASE (users, organizations, organization_members)
-- ============================================================

CREATE TABLE IF NOT EXISTS organizations (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  type varchar(50) NOT NULL DEFAULT 'productora',
  name varchar(255) NOT NULL,
  slug varchar(255) NOT NULL,
  legal_name varchar(255) DEFAULT NULL,
  bio_short text DEFAULT NULL,
  bio_long longtext DEFAULT NULL,
  description text DEFAULT NULL,
  website varchar(255) DEFAULT NULL,
  email varchar(255) DEFAULT NULL,
  phone varchar(50) DEFAULT NULL,
  province_id bigint unsigned DEFAULT NULL,
  city varchar(100) DEFAULT NULL,
  address varchar(255) DEFAULT NULL,
  logo_path varchar(255) DEFAULT NULL,
  banner_path varchar(255) DEFAULT NULL,
  logo_media_id bigint unsigned DEFAULT NULL,
  cover_media_id bigint unsigned DEFAULT NULL,
  social_links json DEFAULT NULL,
  is_verified tinyint(1) NOT NULL DEFAULT 0,
  status varchar(30) NOT NULL DEFAULT 'active',
  created_by bigint unsigned DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY organizations_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organization_members (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  organization_id bigint unsigned NOT NULL,
  user_id bigint unsigned NOT NULL,
  role varchar(50) NOT NULL DEFAULT 'member',
  status varchar(30) NOT NULL DEFAULT 'active',
  invited_at timestamp NULL DEFAULT NULL,
  accepted_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY org_member_unique (organization_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLAS DE CONTENIDO
-- ============================================================

-- events table
CREATE TABLE IF NOT EXISTS events (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  organization_id bigint unsigned DEFAULT NULL,
  venue_id bigint unsigned DEFAULT NULL,
  title varchar(255) NOT NULL,
  subtitle varchar(255) DEFAULT NULL,
  excerpt text DEFAULT NULL,
  body longtext DEFAULT NULL,
  event_type varchar(50) NOT NULL DEFAULT 'recital',
  modality varchar(30) NOT NULL DEFAULT 'presencial',
  slug varchar(255) DEFAULT NULL,
  start_at timestamp NULL DEFAULT NULL,
  end_at timestamp NULL DEFAULT NULL,
  timezone varchar(50) NOT NULL DEFAULT 'America/Argentina/Buenos_Aires',
  province_id bigint unsigned DEFAULT NULL,
  city varchar(255) DEFAULT NULL,
  address varchar(255) DEFAULT NULL,
  latitude decimal(10,8) DEFAULT NULL,
  longitude decimal(11,8) DEFAULT NULL,
  ticket_url varchar(255) DEFAULT NULL,
  price_text varchar(255) DEFAULT NULL,
  is_free tinyint(1) NOT NULL DEFAULT 0,
  capacity int DEFAULT NULL,
  status varchar(30) NOT NULL DEFAULT 'active',
  editorial_status varchar(30) NOT NULL DEFAULT 'draft',
  publication_mode varchar(40) NOT NULL DEFAULT 'portal_only',
  featured_image_id bigint unsigned DEFAULT NULL,
  featured_image_path varchar(255) DEFAULT NULL,
  seo_title varchar(255) DEFAULT NULL,
  meta_description text DEFAULT NULL,
  approved_by bigint unsigned DEFAULT NULL,
  approved_at timestamp NULL DEFAULT NULL,
  published_at timestamp NULL DEFAULT NULL,
  created_by bigint unsigned DEFAULT NULL,
  `show` tinyint(1) DEFAULT NULL,
  detalles text DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY events_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- venues table
CREATE TABLE IF NOT EXISTS venues (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  slug varchar(255) DEFAULT NULL,
  description text DEFAULT NULL,
  address varchar(255) DEFAULT NULL,
  city varchar(255) DEFAULT NULL,
  province_id bigint unsigned DEFAULT NULL,
  latitude decimal(10,8) DEFAULT NULL,
  longitude decimal(11,8) DEFAULT NULL,
  capacity int DEFAULT NULL,
  phone varchar(255) DEFAULT NULL,
  website varchar(255) DEFAULT NULL,
  status varchar(30) NOT NULL DEFAULT 'active',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- news table
CREATE TABLE IF NOT EXISTS news (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  organization_id bigint unsigned DEFAULT NULL,
  title varchar(255) NOT NULL,
  slug varchar(255) DEFAULT NULL,
  subtitle varchar(255) DEFAULT NULL,
  excerpt text DEFAULT NULL,
  body longtext DEFAULT NULL,
  news_type varchar(50) NOT NULL DEFAULT 'general',
  editorial_status varchar(30) NOT NULL DEFAULT 'draft',
  publication_mode varchar(40) NOT NULL DEFAULT 'portal_only',
  featured_image_id bigint unsigned DEFAULT NULL,
  featured_image_path varchar(255) DEFAULT NULL,
  seo_title varchar(255) DEFAULT NULL,
  meta_description text DEFAULT NULL,
  approved_by bigint unsigned DEFAULT NULL,
  approved_at timestamp NULL DEFAULT NULL,
  published_at timestamp NULL DEFAULT NULL,
  created_by bigint unsigned DEFAULT NULL,
  estado tinyint(1) NOT NULL DEFAULT 1,
  interprete_id bigint unsigned DEFAULT NULL,
  categoria_id int NOT NULL DEFAULT 1,
  visitas int NOT NULL DEFAULT 0,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY news_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- media_assets table
CREATE TABLE IF NOT EXISTS media_assets (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  imageable_type varchar(255) DEFAULT NULL,
  imageable_id bigint unsigned DEFAULT NULL,
  profile varchar(255) DEFAULT NULL,
  original_path varchar(255) NOT NULL,
  variants_json longtext DEFAULT NULL,
  alt varchar(255) DEFAULT NULL,
  sort_order int NOT NULL DEFAULT 0,
  original_width int DEFAULT NULL,
  original_height int DEFAULT NULL,
  mime varchar(255) DEFAULT NULL,
  disk varchar(255) NOT NULL DEFAULT 'public',
  original_name varchar(255) DEFAULT NULL,
  size bigint unsigned DEFAULT NULL,
  caption varchar(255) DEFAULT NULL,
  `group` varchar(50) DEFAULT NULL,
  created_by bigint unsigned DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLAS DE MODERACIÓN Y SOCIALES
-- ============================================================

CREATE TABLE IF NOT EXISTS event_interprete (
  event_id bigint unsigned NOT NULL,
  interprete_id bigint unsigned NOT NULL,
  role varchar(100) DEFAULT NULL,
  sort_order int NOT NULL DEFAULT 0,
  PRIMARY KEY (event_id, interprete_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS moderation_reviews (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  content_type varchar(100) NOT NULL,
  content_id bigint unsigned NOT NULL,
  reviewer_user_id bigint unsigned DEFAULT NULL,
  action varchar(30) NOT NULL,
  comments text DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS social_accounts (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  owner_type varchar(255) NOT NULL,
  owner_id bigint unsigned NOT NULL,
  provider varchar(50) NOT NULL,
  account_name varchar(255) NOT NULL,
  account_external_id varchar(255) NOT NULL,
  page_or_profile_name varchar(255) DEFAULT NULL,
  token_encrypted text DEFAULT NULL,
  refresh_token_encrypted text DEFAULT NULL,
  token_expires_at timestamp NULL DEFAULT NULL,
  scopes_json json DEFAULT NULL,
  status varchar(30) NOT NULL DEFAULT 'active',
  last_checked_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLAS DE PASARELA (publicación)
-- ============================================================

CREATE TABLE IF NOT EXISTS publication_requests (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  content_type varchar(100) NOT NULL,
  content_id bigint unsigned NOT NULL,
  requested_by bigint unsigned NOT NULL,
  mode varchar(50) NOT NULL,
  wants_portal_publish tinyint(1) NOT NULL DEFAULT 0,
  wants_portal_social tinyint(1) NOT NULL DEFAULT 0,
  wants_own_social tinyint(1) NOT NULL DEFAULT 0,
  scheduled_at timestamp NULL DEFAULT NULL,
  reminder_policy varchar(50) DEFAULT NULL,
  status varchar(30) NOT NULL DEFAULT 'pending',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS publication_targets (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  publication_request_id bigint unsigned NOT NULL,
  provider varchar(50) NOT NULL,
  social_account_id bigint unsigned DEFAULT NULL,
  destination_type varchar(50) DEFAULT NULL,
  template_variant varchar(50) DEFAULT NULL,
  scheduled_at timestamp NULL DEFAULT NULL,
  status varchar(30) NOT NULL DEFAULT 'pending',
  priority int NOT NULL DEFAULT 0,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS publication_attempts (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  publication_target_id bigint unsigned NOT NULL,
  attempt_number int NOT NULL DEFAULT 1,
  started_at timestamp NOT NULL DEFAULT current_timestamp(),
  finished_at timestamp NULL DEFAULT NULL,
  request_payload_json json DEFAULT NULL,
  response_payload_json json DEFAULT NULL,
  external_post_id varchar(255) DEFAULT NULL,
  external_url varchar(255) DEFAULT NULL,
  status varchar(30) NOT NULL DEFAULT 'processing',
  error_code varchar(255) DEFAULT NULL,
  error_message text DEFAULT NULL,
  is_retryable tinyint(1) NOT NULL DEFAULT 0,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS publication_templates (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  content_type varchar(100) DEFAULT NULL,
  provider varchar(50) NOT NULL,
  variant_name varchar(100) NOT NULL DEFAULT 'default',
  template_text text NOT NULL,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICACIONES, AUDITORÍA Y COLAS
-- ============================================================

CREATE TABLE IF NOT EXISTS notifications (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint unsigned NOT NULL,
  type varchar(100) NOT NULL,
  title varchar(255) NOT NULL,
  body text NOT NULL,
  action_url varchar(255) DEFAULT NULL,
  is_read tinyint(1) NOT NULL DEFAULT 0,
  read_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint unsigned DEFAULT NULL,
  action varchar(100) NOT NULL,
  entity_type varchar(100) DEFAULT NULL,
  entity_id bigint unsigned DEFAULT NULL,
  old_values json DEFAULT NULL,
  new_values json DEFAULT NULL,
  ip_address varchar(45) DEFAULT NULL,
  user_agent text DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jobs (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  queue varchar(255) NOT NULL,
  payload longtext NOT NULL,
  attempts tinyint unsigned NOT NULL,
  reserved_at int unsigned DEFAULT NULL,
  available_at int unsigned NOT NULL,
  created_at int unsigned NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ALTER: campos publisher en users (tabla ya existente)
-- ============================================================
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS points int NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS rank varchar(255) NOT NULL DEFAULT 'Colaborador Bronce',
  ADD COLUMN IF NOT EXISTS phone varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS status varchar(30) NOT NULL DEFAULT 'active',
  ADD COLUMN IF NOT EXISTS is_verified_publisher tinyint(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS publisher_type_default varchar(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS last_login_at timestamp NULL DEFAULT NULL;

-- ============================================================
-- MIGRATIONS: marcar todas como ejecutadas
-- ============================================================

INSERT IGNORE INTO migrations (migration, batch) VALUES
  ('2026_03_22_041551_create_categories_and_classifieds_tables', 2),
  ('2026_03_23_024951_create_newsletter_subscribers_table', 2),
  ('2026_04_09_031600_add_publisher_fields_to_users_table', 3),
  ('2026_04_09_032400_create_organizations_table', 2),
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

SET FOREIGN_KEY_CHECKS=1;
