# Contenidos vinculados públicos en la ficha de festival

## Contexto y aprobación

El backend permite vincular artistas, noticias, eventos y evergreen, pero la ficha los oculta detrás del piloto Festival Vivo. El usuario aprobó el 2026-10-03 hacerlos visibles y crear una rama desde dev. Rama: `codex/festival-related-content`.

El usuario aprobó además resolver los conflictos con dev, registrar el cambio en CHANGELOG, hacer commit e integrar a dev. Se preservan las relaciones públicas de Evento incorporadas por el otro cambio y la condición de hora de la tarjeta; se conservan ambos registros del estado actual.

## Alcance

Mostrar los cuatro bloques en cualquier festival publicado cuando haya relaciones explícitas elegibles. Mantener los relacionados por provincia y mes. Paginar próximas fechas (tres por página), conservar título y hora de cada jornada y evitar cargas duplicadas. No modifica datos, migraciones, URLs existentes ni los gates del piloto en Evento/Artista.

## Archivos afectados

- `app/Services/Product/FestivalJourneyService.php`
- `app/Services/Product/FestivalJourney.php`
- `app/Http/Controllers/Frontend/FestivalesController.php`
- `resources/views/frontend/festivales/show.blade.php`
- `resources/views/components/show-card.blade.php`
- `config/features.php`
- `tests/Feature/Festivals/FestivalJourneyServiceTest.php`
- `tests/Feature/Festivals/FestivalJourneyFrontendTest.php`
- `tests/Feature/Festivals/FestivalPublicRelatedContentTest.php`
- `project/docs/00_estado_actual.md`

## Impacto

Navegación interna y descubrimiento editorial; sin publicación automática ni inferencia de grilla. Una relación Festival–Artista no acredita participación en la próxima edición. Jesús María local no tiene relaciones cargadas: este cambio no agrega contenido.
