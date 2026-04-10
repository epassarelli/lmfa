-- ============================================================
-- PATCH: columnas faltantes en tabla news (de la tabla noticias original)
-- Compatible con MariaDB 10.8+ (ADD COLUMN IF NOT EXISTS)
-- ============================================================

ALTER TABLE news
  ADD COLUMN IF NOT EXISTS interprete_id bigint unsigned DEFAULT NULL AFTER created_by,
  ADD COLUMN IF NOT EXISTS categoria_id int NOT NULL DEFAULT 1 AFTER interprete_id,
  ADD COLUMN IF NOT EXISTS visitas int NOT NULL DEFAULT 0 AFTER categoria_id;
