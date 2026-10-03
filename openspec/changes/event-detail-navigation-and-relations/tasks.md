# Tareas: event-detail-navigation-and-relations

> Ejecutar solo después de la aprobación. Estados: `pending`, `in_progress`, `blocked`, `done`, `needs_review`.

## 0. Previo

- [x] 0.1 Auditoría read-only (2026-10-03, `project/docs/sql/audit_eventos_cobertura.out.txt`) de cobertura de eventos publicados: % con `ticket_url`, `price_text`, `is_free`, `address`, `venue_id`, `end_at`, imagen, intérpretes, festival, artículo y peña.
- [x] 0.2 Respuestas a las preguntas abiertas de `proposal.md`.

## 1. Fase 1: ficha usable

- [x] 1.1 Extraer los filtros a `resources/views/frontend/shows/_filters.blade.php` (variantes `full` y `compact`) y usarlos en `index` y `show`.
- [x] 1.2 Quitar el contenedor anidado y la columna `lg:w-2/3` en `shows/show.blade.php`.
- [x] 1.3 Componente de ficha técnica: fecha y hora, lugar o venue, dirección, provincia, precio o gratis, entradas, cómo llegar.
- [x] 1.4 Artistas siempre visibles (fuera del flag Festival Vivo).
- [x] 1.5 Aviso de evento realizado.
- [x] 1.6 Breadcrumb con provincia.
- [x] 1.7 Bloques "Más de {Artista}" (prioritario) y "Próximos eventos en {Provincia}" (`limit` en SQL, eager loading).
- [x] 1.8 "Explorar por provincia" con provincias con eventos futuros y su conteo, en caché; aplicarlo también al índice.
- [x] 1.9 Cachear la resolución de provincia por slug en `findProvinciaBySlug()`.
- [x] 1.10 JSON-LD completo de la ficha.
- [x] 1.11 Quitar `$ultimos_shows` y `$noticiasRelacionadas`.
- [x] 1.12 Tests Feature y QA visual.

## 1b. Datos

- [ ] 1b.1 Script read-only de propuesta `city` → `province_id`, con salida `.sql` revisable (sin ejecutar). — fuera de alcance (lo gestiona el usuario).

## 2. Fase 2: relaciones

- [x] 2.1 `Event::knowledgeArticles()`.
- [x] 2.2 Servicio `EventRelatedContentService`: festivales, artículos, peñas (con flag) y noticias derivadas, con límites y deduplicación.
- [x] 2.3 Bloques en la ficha con `x-content-journey.section` y módulos `event_related_*`.
- [x] 2.4 (Aprobada) Backend: Select2 de festivales, artículos y peñas, más `end_at` y `excerpt`, con sincronización de pivots.
- [x] 2.5 Tests Feature y QA.

## 3. Cierre

- [x] 3.1 Revisión de performance: consultas y peso del HTML, antes y después.
- [x] 3.2 Actualizar `project/docs/00_estado_actual.md`.

## 4. Ajustes post-QA (aprobados por el usuario en chat, 2026-10-03)

- [x] 4.1 La ficha reutiliza el buscador completo del índice (mismo parcial `_filters`, misma variante); se descarta la variante compacta.
- [x] 4.2 `EditorialImageResolver`: si la imagen propia (media o legacy) o la del artista relacionado no existe en disco, se usa el fallback editorial. `optimized-image` toma el primer grupo de variantes no vacío.
