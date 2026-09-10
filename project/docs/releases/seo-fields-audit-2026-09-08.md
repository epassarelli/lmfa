# Auditoría simple de campos SEO — 2026-09-08

**Conclusión:** la carencia generalizada de la primera auditoría ya está mayormente resuelta en el código. Las 12 entidades editoriales principales tienen `excerpt`, `seo_title` y `meta_description`. Falta terminar de unificar su edición y uso público.

Alcance: revisión de modelos, migraciones, validaciones, formularios, rutas, controladores y plantillas locales. Docker no está disponible: no se verificó el esquema real ni el porcentaje de registros completos, y no se auditó producción. No se modificó funcionalidad SEO.

| Entidades | Campos básicos | Diferencia pendiente |
|---|---|---|
| Evergreen / Knowledge | Los tres, imagen/alt y datos editoriales | Sigue siendo la referencia más completa; los campos SEO admiten valores vacíos. |
| Artistas, Festivales, Recetas, Mitos, Discos y Canciones | Los tres en modelos, migraciones y formularios | La bajada no se presenta uniformemente: varias fichas usan el cuerpo directamente aunque tienen `excerpt`. |
| Noticias y Eventos | Los tres en modelos, migraciones y validación API | Los formularios administrativos actuales no ofrecen controles para los tres campos. |
| Peñas, Radios y Programas | Los tres en modelos, migraciones y formularios | Sus controladores generan metadatos por separado, sin pasar por la misma selección/limpieza/recorte de `SeoMetadata`. |
| Categorías Knowledge | `seo_title`, `meta_description` y `description` | `description` cumple el papel de introducción de categoría; no hace falta duplicarlo como `excerpt`. |
| Clasificados | Título, slug y descripción; sin los tres campos separados | Índice y ficha declaran `title`/`meta_description`, pero el layout consume `metaTitle`/`metaDescription`: termina usando valores genéricos. Prioridad alta. |

Entrevistas y Videos no constituyen módulos públicos consolidados: los controladores legacy no permiten certificar una entidad completa. Provincias, localidades, categorías auxiliares, organizaciones, recintos, relaciones y entidades operativas no necesitan copiar todos los campos de un artículo cuando no tienen ficha pública propia. Las landings de filtros requieren criterio por página, no columnas SEO en cada tabla auxiliar.

## Qué unificar primero

1. Corregir la conexión de metadatos de Clasificados y habilitar su edición en Noticias/Eventos.
2. Usar un criterio compartido: título SEO → título; descripción SEO → bajada → texto limpio. Hay una buena base en `app/Support/SeoMetadata.php`.
3. Distinguir bajada visible (`excerpt`) de descripción para buscadores (`meta_description`). Mostrar la bajada de manera coherente en fichas y tarjetas.
4. Uniformar el manejo de imagen, texto alternativo y vista previa social. Hoy conviven `foto`, media con `alt` y `featured_image_path`/`image_alt`; no todos necesitan una columna duplicada.
5. Auditar luego contenido real: vacíos, duplicados y calidad de los resúmenes, incluyendo autor/revisión y fecha de verificación cuando correspondan.

Los validadores modernos generalmente aceptan 1.000 caracteres de bajada, 255 de título SEO y 320 de descripción. `SeoMetadata` recorta las descripciones a unos 160, mientras Peñas/Radios/Programas no aplican el mismo recorte. Es una inconsistencia interna a resolver, no un límite impuesto por Google: [Google explica que la descripción no tiene un máximo fijo y que el snippet se adapta a la pantalla](https://developers.google.com/search/docs/appearance/snippet).

Performance: unificar estos valores ya cargados en el modelo no necesita consultas nuevas ni JavaScript. Evitar obtener resúmenes o imágenes con consultas por tarjeta. Esta auditoría no midió TTFB ni Core Web Vitals.

Evidencia principal: `app/Models/*`, `database/migrations/*modernize*`, `app/Http/Requests/Api/*`, `resources/views/backend/news/form.blade.php`, `resources/views/backend/events/form.blade.php`, `resources/views/frontend/classifieds/{index,show}.blade.php`, `resources/views/layouts/app.blade.php`, `app/Support/SeoMetadata.php` y controladores públicos de directorios.
