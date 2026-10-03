-- Auditoría READ-ONLY de cobertura de eventos (cambio event-detail-navigation-and-relations).
-- Solo SELECT. No modifica datos.
-- Uso (PowerShell, desde C:\proyectos\lmfa):
--   Get-Content project/docs/sql/audit_eventos_cobertura.sql | docker exec -i lmfa-db-1 sh -c 'mysql --force -t -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > project/docs/sql/audit_eventos_cobertura.out.txt

SELECT '1. Totales' AS seccion;
SELECT
  COUNT(*) AS total,
  SUM(editorial_status = 'published' AND status = 'active' AND (published_at IS NULL OR published_at <= NOW())) AS publicos,
  SUM(editorial_status = 'published' AND status = 'active' AND (published_at IS NULL OR published_at <= NOW()) AND start_at >= CURDATE()) AS publicos_futuros
FROM events;

SELECT '2. Completitud de campos (eventos publicos)' AS seccion;
SELECT
  COUNT(*) AS publicos,
  SUM(end_at IS NOT NULL) AS con_end_at,
  SUM(NULLIF(TRIM(excerpt), '') IS NOT NULL) AS con_excerpt,
  SUM(province_id IS NOT NULL) AS con_provincia,
  SUM(NULLIF(TRIM(city), '') IS NOT NULL) AS con_ciudad,
  SUM(NULLIF(TRIM(address), '') IS NOT NULL) AS con_direccion,
  SUM(venue_id IS NOT NULL) AS con_venue,
  SUM(organization_id IS NOT NULL) AS con_organizacion,
  SUM(latitude IS NOT NULL AND longitude IS NOT NULL) AS con_coordenadas,
  SUM(NULLIF(TRIM(ticket_url), '') IS NOT NULL) AS con_ticket_url,
  SUM(NULLIF(TRIM(price_text), '') IS NOT NULL) AS con_precio,
  SUM(is_free = 1) AS gratuitos,
  SUM(NULLIF(TRIM(featured_image_path), '') IS NOT NULL OR featured_image_id IS NOT NULL) AS con_imagen_destacada
FROM events
WHERE editorial_status = 'published' AND status = 'active' AND (published_at IS NULL OR published_at <= NOW());

SELECT '3. Relaciones (eventos publicos con al menos una)' AS seccion;
SELECT
  (SELECT COUNT(DISTINCT ei.event_id) FROM event_interprete ei JOIN events e ON e.id = ei.event_id WHERE e.editorial_status = 'published' AND e.status = 'active') AS con_interpretes,
  (SELECT COUNT(DISTINCT ef.event_id) FROM event_festival ef JOIN events e ON e.id = ef.event_id WHERE e.editorial_status = 'published' AND e.status = 'active') AS con_festival,
  (SELECT COUNT(DISTINCT ek.event_id) FROM event_knowledge_article ek JOIN events e ON e.id = ek.event_id WHERE e.editorial_status = 'published' AND e.status = 'active') AS con_enciclopedia,
  (SELECT COUNT(DISTINCT pe.event_id) FROM penia_profile_event pe JOIN events e ON e.id = pe.event_id WHERE e.editorial_status = 'published' AND e.status = 'active') AS con_penia;

SELECT '4. Eventos futuros publicos por provincia' AS seccion;
SELECT COALESCE(p.nombre, '(sin provincia)') AS provincia, COUNT(*) AS futuros
FROM events e LEFT JOIN provincias p ON p.id = e.province_id
WHERE e.editorial_status = 'published' AND e.status = 'active' AND (e.published_at IS NULL OR e.published_at <= NOW()) AND e.start_at >= CURDATE()
GROUP BY provincia ORDER BY futuros DESC;

SELECT '5. Colisiones slug de evento = slug de provincia' AS seccion;
-- provincias no tiene columna slug (es un accessor Str::slug(nombre)); comparación aproximada sin acentos.
SELECT e.id, e.slug, p.nombre FROM events e JOIN provincias p
  ON e.slug = LOWER(REPLACE(CONVERT(p.nombre USING ascii), ' ', '-'))
  OR e.slug = LOWER(REPLACE(p.nombre, ' ', '-'));

SELECT '6. Imagenes en media_assets (puede fallar si la tabla no existe)' AS seccion;
SELECT COUNT(DISTINCT imageable_id) AS eventos_con_media FROM media_assets WHERE imageable_type LIKE '%Event';
