## Why

Se está contactando a intérpretes y festivales para que envíen información. Hoy no hay una página pública que explique qué se puede publicar, qué datos hacen falta y con qué criterios edita el portal. Una guía única reduce idas y vueltas por mail, ordena lo que se recibe y se puede linkear en cada contacto.

## What Changes

- Nueva página pública y estática `GET /guia-para-publicar` ("Guía para publicar en Mi Folklore Argentino") sobre el layout público, con SEO básico y canonical.
- Una sección por tipo de contenido: eventos y fechas, festivales, artistas (perfil y biografía), noticias y gacetillas, discos y canciones. Cada una con datos requeridos y recomendados.
- Secciones transversales: imágenes, criterios editoriales, tiempos, cómo enviar (cuenta del portal o mail).
- Enlace desde el footer y entrada en el sitemap de páginas estáticas.
- Test Feature de la página, el enlace del footer y el sitemap.

## Capabilities

### New Capabilities
- `publish-guide`: página pública de guía de publicación.

## Impact

- `routes/web.php` (ruta declarada antes de la ruta dinámica `/{interprete:slug}`), nuevo controlador y vista Blade, `layouts/partials/footer.blade.php`, `SitemapController::staticEntries`.
- Sin migraciones, sin cambios de modelos ni de permisos.
- Los criterios concretos (anticipación, medidas de imagen, longitud de biografías) son propuesta editorial pendiente de confirmación humana; viven solo en la vista.
- Fuera de alcance: formularios, registro, permisos por organización, directorio de contactos, letras completas (derechos).
