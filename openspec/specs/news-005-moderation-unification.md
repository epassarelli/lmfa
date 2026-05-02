# Cambio: NEWS-005 — Unificación de ModerationController

## Objetivo
Centralizar la publicación de noticias en estado borrador a través del `NewsService`.

## Problema actual
`ModerationController` realiza actualizaciones directas a la base de datos (`update(['editorial_status' => 'published'])`). Esto salta cualquier lógica de re-procesamiento o validación final que pueda requerirse en el futuro.

## Alcance incluido
- Refactorización del método `publish` en `ModerationController`.
- Uso del `NewsService` para asegurar que el cambio de estado respete las reglas de negocio (ej: asignación de `published_at`).

## Archivos afectados
- `app/Http/Controllers/Backend/ModerationController.php`

## Reglas funcionales
- Delegar la lógica de publicación al `NewsService`.
- Asegurar que el moderador que hace clic quede registrado como `approved_by`.

## Criterio de aceptación
- Publicar una noticia desde la bandeja de moderación funciona correctamente.
- La noticia queda visible en el frontend inmediatamente tras la acción.
