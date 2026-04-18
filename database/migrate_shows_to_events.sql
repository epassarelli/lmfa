-- =============================================================================
-- Migración de datos: shows → events
-- =============================================================================
-- Copia los registros de la tabla legacy 'shows' a la nueva tabla 'events',
-- mapeando los nombres de columnas al nuevo esquema de la Pasarela de Contenidos.
--
-- SEGURO: usa INSERT ... WHERE NOT EXISTS para evitar duplicados.
-- =============================================================================

-- Verificar estado antes
SELECT 'Registros en shows:' AS info, COUNT(*) AS total FROM shows;
SELECT 'Registros en events (antes):' AS info, COUNT(*) AS total FROM events;

-- Migración principal
INSERT INTO events (
    id,
    title,
    slug,
    body,
    featured_image_path,
    start_at,
    address,
    city,
    province_id,
    created_by,
    status,
    editorial_status,
    publication_mode,
    created_at,
    updated_at,
    `show`,
    detalles
)
SELECT
    s.id,
    s.show,
    s.slug,
    s.detalle,
    s.foto,
    s.fecha,
    s.direccion,
    s.lugar, -- Mapeamos 'lugar' a 'city' o similar si no hay venue_id
    NULL,    -- province_id se puede mapear si se conoce
    s.user_id,
    CASE WHEN s.estado = 1 THEN 'active' ELSE 'inactive' END,
    CASE WHEN s.estado = 1 THEN 'published' ELSE 'draft' END,
    'portal_only',
    s.created_at,
    s.updated_at,
    1,       -- flag 'show'
    s.detalle
FROM shows s
WHERE NOT EXISTS (
    SELECT 1 FROM events ex WHERE ex.id = s.id
)
ORDER BY s.id;

-- Migrar relación con intérpretes (tabla pivote event_interprete)
-- Asumimos que la tabla shows tenía un interprete_id directo.
INSERT IGNORE INTO event_interprete (event_id, interprete_id, sort_order)
SELECT id, interprete_id, 0
FROM shows
WHERE interprete_id IS NOT NULL;

-- Verificar estado después
SELECT 'Registros en events (después):' AS info, COUNT(*) AS total FROM events;
