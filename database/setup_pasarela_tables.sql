SET FOREIGN_KEY_CHECKS=0;

-- Fix missing unique on organization_members
ALTER TABLE organization_members ADD UNIQUE KEY org_member_unique (organization_id, user_id);

-- events table (fresh, alongside existing shows)
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
  address varchar(255) DEFAULT NULL,
  city varchar(255) DEFAULT NULL,
  province_id bigint unsigned DEFAULT NULL,
  latitude decimal(10,8) DEFAULT NULL,
  longitude decimal(11,8) DEFAULT NULL,
  capacity int DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- news table (pasarela, separate from noticias)
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
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY news_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- media_assets table (pasarela, separate from images)
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

-- event_interprete pivot
CREATE TABLE IF NOT EXISTS event_interprete (
  event_id bigint unsigned NOT NULL,
  interprete_id bigint unsigned NOT NULL,
  role varchar(100) DEFAULT NULL,
  sort_order int NOT NULL DEFAULT 0,
  PRIMARY KEY (event_id, interprete_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- moderation_reviews
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

-- social_accounts
CREATE TABLE IF NOT EXISTS social_accounts (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  owner_type varchar(255) NOT NULL,
  owner_id bigint unsigned NOT NULL,
  provider varchar(50) NOT NULL,
  account_name varchar(255) NOT NULL,
  account_external_id varchar(255) NOT NULL,
  page_or_profile_name varchar(255) DEFAULT NULL,
  token_encrypted text DEFAULT NULL,
  token_expires_at timestamp NULL DEFAULT NULL,
  scopes_json json DEFAULT NULL,
  status varchar(30) NOT NULL DEFAULT 'active',
  last_checked_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- publication_requests
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

-- publication_targets
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

-- publication_attempts
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

-- publication_templates
CREATE TABLE IF NOT EXISTS publication_templates (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  content_type varchar(100) NOT NULL,
  provider varchar(50) NOT NULL,
  variant_name varchar(50) NOT NULL DEFAULT 'default',
  template_text text NOT NULL,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- notifications
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

-- audit_logs
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

-- jobs table for Laravel Queue
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

-- Mark all pending migrations as done (batch 3)
INSERT IGNORE INTO migrations (migration, batch) VALUES
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
