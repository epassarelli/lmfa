# Cambio: NEWS-004 — Integración de ContributionController

## Objetivo
Unificar la aprobación de colaboraciones ciudadanas utilizando el `NewsService` para garantizar consistencia técnica.

## Problema actual
Actualmente `ContributionController` crea modelos `News` manualmente. Esto causa que las noticias enviadas por el público NO tengan versiones WebP, no se validen los slugs y se ignoren las reglas de negocio centralizadas.

## Alcance incluido
- Modificación del método `approve()` en el controlador administrativo de contribuciones.
- Mapeo del `payload` (JSON) al formato requerido por el servicio.
- Gestión de la transición de estado de la `Contribution` de `pending` a `approved`.

## Fuera de alcance
- No se modifica el formulario de envío en el frontend.

## Archivos afectados
- `app/Http/Controllers/Backend/ContributionController.php`

## Reglas funcionales
- Al aprobar, se debe pasar el `user_id` del colaborador como `created_by`.
- Se debe pasar el `auth()->id()` del administrador como `approved_by`.
- La fuente de imagen será el campo `foto` del payload (que puede ser una ruta previa).

## Validación
- Aprobar una contribución y verificar que el registro resultante en la tabla `news` tenga un `editorial_status = 'published'` y que existan registros asociados en `media_assets`.

## Criterio de aceptación
- El flujo de aprobación funciona sin errores 500.
- Todas las noticias aprobadas pasan por el procesamiento de imágenes optimizadas.
