-- =============================================================================
-- Migración de datos: noticias → news
-- =============================================================================
-- Copia los 347 registros de la tabla legacy 'noticias' a la nueva tabla 'news',
-- mapeando los nombres de columnas al nuevo esquema.
--
-- SEGURO: usa INSERT ... WHERE NOT EXISTS para evitar duplicados si se ejecuta
-- varias veces. No modifica ni elimina la tabla 'noticias'.
--
-- Cómo ejecutar:
--   docker exec -i lmfa-db-1 mysql -umfa -pmfa mfa < database/migrate_noticias_to_news.sql
-- O bien desde phpMyAdmin: pegar el contenido y ejecutar.
-- =============================================================================

-- Verificar estado antes
SELECT 'Registros en noticias:' AS info, COUNT(*) AS total FROM noticias;
SELECT 'Registros en news (antes):' AS info, COUNT(*) AS total FROM news;

-- Migración principal
INSERT INTO news (
    id,
    title,
    slug,
    body,
    featured_image_path,
    created_by,
    published_at,
    interprete_id,
    visitas,
    categoria_id,
    estado,
    editorial_status,
    news_type,
    publication_mode,
    created_at,
    updated_at
)
SELECT
    n.id,
    n.titulo,
    n.slug,
    n.noticia,
    n.foto,
    n.user_id,
    n.publicar,
    n.interprete_id,
    n.visitas,
    n.categoria_id,
    n.estado,
    CASE WHEN n.estado = 1 THEN 'published' ELSE 'draft' END AS editorial_status,
    'general'      AS news_type,
    'portal_only'  AS publication_mode,
    n.created_at,
    n.updated_at
FROM noticias n
WHERE NOT EXISTS (
    SELECT 1 FROM news nx WHERE nx.id = n.id
)
ORDER BY n.id;

-- Migrar relaciones de intérpretes secundarios (tabla pivote interprete_noticia)
-- La tabla usa noticia_id → que en ambos esquemas referencia el mismo id de registro.
-- No es necesario migrar datos del pivote porque los IDs se preservan.

-- Verificar estado después
SELECT 'Registros en news (después):' AS info, COUNT(*) AS total FROM news;

-- Verificación cruzada: debe ser 0 si todo migró correctamente
SELECT 'Registros en noticias SIN contraparte en news:' AS info, COUNT(*) AS faltantes
FROM noticias n
WHERE NOT EXISTS (SELECT 1 FROM news nx WHERE nx.id = n.id);
