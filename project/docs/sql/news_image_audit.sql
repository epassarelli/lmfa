-- Auditoria no destructiva para diagnosticar imagenes de noticias

SELECT COUNT(*) AS published_total
FROM news
WHERE editorial_status = 'published';

SELECT COUNT(*) AS published_with_media_assets
FROM news n
WHERE n.editorial_status = 'published'
  AND EXISTS (
    SELECT 1
    FROM media_assets ma
    WHERE ma.imageable_id = n.id
      AND ma.imageable_type = 'App\\Models\\News'
  );

SELECT COUNT(*) AS published_with_legacy_featured_image
FROM news
WHERE editorial_status = 'published'
  AND featured_image_path IS NOT NULL
  AND featured_image_path <> '';

SELECT id, slug, title, featured_image_path
FROM news n
WHERE n.editorial_status = 'published'
  AND NOT EXISTS (
    SELECT 1
    FROM media_assets ma
    WHERE ma.imageable_id = n.id
      AND ma.imageable_type = 'App\\Models\\News'
  )
  AND (
    n.featured_image_path IS NULL
    OR n.featured_image_path = ''
    OR n.featured_image_path LIKE '/tmp/%'
  )
ORDER BY id DESC;
