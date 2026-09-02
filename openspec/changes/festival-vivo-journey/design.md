## Context

`BL-0022C` define un piloto de descubrimiento desde un Festival evergreen hacia Eventos futuros y Artistas activos. Las relaciones `event_festival`, `event_interprete`, `festival_interprete`, `festival_news` y `knowledge_article_festival` ya existen, pero las vistas actuales mezclan filtros, cargan colecciones completas y no disponen de un gate de piloto.

## Goals / Non-Goals

**Goals:** reutilizar relaciones explícitas, evitar N+1, mantener SEO canónico, medir el recorrido y permitir rollback inmediato mediante configuración.

**Non-Goals:** Peñas, Radios, recomendaciones personalizadas, backfill automático, compra de entradas, nuevas URLs o migraciones.

## Decisions

- Un `FestivalJourneyService` devolverá colecciones ya filtradas y limitadas; las vistas no consultarán relaciones.
- `features.festival_journey` y una allowlist de IDs de Festival protegerán las tres superficies de forma transitiva.
- Solo los Eventos `publiclyVisible()` y futuros, Artistas activos, Noticias publicadas y evergreen visible podrán renderizarse.
- Componentes Blade semánticos renderizarán bloques únicamente cuando tengan elementos; los enlaces conservarán `href` y añadirán atributos de analítica.
- El rollback apaga el flag; no modifica pivots ni contenido.

## Risks / Trade-offs

- [Menos de dos Festivales elegibles] -> el flag se mantiene apagado y no se amplía el piloto.
- [Consultas crecientes] -> eager loading, límites SQL y pruebas con lazy loading bloqueado.
- [Relaciones engañosas] -> no se infieren por texto ni geografía.
- [SEO o analítica defectuosos] -> no se crean URLs nuevas; enlaces HTML y eventos sin datos personales.

## Migration Plan

1. Implementar servicio, configuración y pruebas con el flag apagado.
2. Integrar Festival, Evento y Artista en PRs pequeños.
3. Auditar cobertura y configurar allowlist de dos a cinco Festivales.
4. Validar siete días antes de decidir expansión o rollback.
