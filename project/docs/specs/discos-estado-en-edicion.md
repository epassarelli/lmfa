# Cambio: discos-estado-en-edicion

Estado: aprobada por el usuario en el chat (2026-11-11). Autonomía: `IA_CON_VALIDACION`, reversible y de bajo riesgo. Complementa `discos-spotify-propuestas.md`.

## Objetivo

1. Permitir que quien puede publicar cambie el estado (Activo/Inactivo) de un disco desde el formulario de crear/editar, sin tener que pasar por el Centro de Moderación.
2. Mostrar la portada actual del disco en miniatura dentro del cuadro de la foto, sin desbordar el campo de subida.

## Problema actual

- `AlbumController::store` y `update` fijan `estado = canPublish() ? 1 : 0` en cada guardado y el formulario no tiene selector. Quien puede publicar activa el disco sin quererlo con cualquier guardado y nunca puede dejarlo inactivo desde ahí. Un disco propuesto como inactivo (por ejemplo por la tarea de Spotify) solo se puede activar desde Moderación, o activándolo por accidente al guardar.
- La miniatura de la portada se declara con `style="height: 36px"`, pero `x-optimized-image` no imprime los atributos extra y renderiza la imagen con `width`/`height` de la variante (hasta 600 px). Resultado: la portada ocupa mucho y se sale del cuadro del upload.

## Alcance incluido

- Selector «Estado» (Activo / Inactivo) en el formulario de Discos, visible solo para quien `canPublish()`.
- `AlbumRequest`: regla `estado` nullable|boolean.
- `AlbumController::store` y `update`: usar el estado enviado cuando el usuario puede publicar.
- Miniatura de portada de tamaño fijo y contenido dentro del cuadro, usando un contenedor propio (sin depender de atributos del componente).
- Test Feature del selector, del guardado y de la miniatura.

## Fuera de alcance

- Cambiar `x-optimized-image` (lo usan otras vistas; se deja como observación).
- Moderación (se mantiene como está) y la API.
- Otros formularios (Canciones, Noticias, etc.).
- Cambiar quién puede publicar o los permisos.

## Archivos afectados

- `resources/views/backend/albunes/form.blade.php`
- `app/Http/Requests/AlbumRequest.php`
- `app/Http/Controllers/Backend/AlbumController.php`
- `tests/Feature/Backend/AlbumStatusFieldTest.php` (nuevo)
- `CHANGELOG.md`, `project/docs/00_estado_actual.md`

## Reglas funcionales

### Quien puede publicar
Dado un usuario con `canPublish()` (administrador o permiso `publish contents`)
Cuando abre crear o editar un disco
Entonces ve el selector Estado: en edición con el valor actual preseleccionado; en alta con «Activo» por defecto.

### Guardar con estado elegido
Dado ese usuario
Cuando guarda con `estado = 0` o `estado = 1`
Entonces el disco queda con ese estado.

### Compatibilidad
Dado un usuario que puede publicar
Cuando guarda sin enviar `estado` (clientes o tests anteriores)
Entonces el disco queda activo, como hasta ahora.

### Quien no puede publicar
Dado un usuario sin permiso de publicar
Cuando abre o guarda el formulario
Entonces no ve el selector, su valor enviado se ignora y el disco queda inactivo, como hasta ahora.

### Portada
Dado un disco con portada
Cuando se abre su edición
Entonces la miniatura mide 56 px de ancho en un contenedor fijo, mantiene la proporción y no empuja ni desborda el campo de subida.

## Reglas técnicas

- Respetar la regla de formularios del backoffice: grilla de 12, `col-12`, sin ayuda fija bajo el campo, obligatorios solo los reales (Estado no es obligatorio).
- No renombrar campos existentes. Nuevo campo: `estado`.
- Sin migraciones ni cambios de esquema.

## Validación

- Test Feature nuevo y tests existentes de formularios y permisos de Discos: `CultureFormsCompactTest`, `TrustedPublishingTest`, `MemberContentAccessTest`, `MemberSelectionScopingTest`.
- Comprobación manual: editar un disco inactivo de la tarea de Spotify, elegir Activo, guardar y ver que sale de Moderación.
- Performance: sin consultas nuevas; la miniatura usa una variante ya generada.

## Riesgos

- Un cliente que enviaba `estado` y esperaba que se ignorara ahora será respetado, pero solo para quien puede publicar.
- Si un publicador guarda desde un formulario anterior en caché sin el campo, el disco queda activo (comportamiento actual).

## Criterio de aceptación

Un administrador puede pasar un disco inactivo a activo, y a la inversa, desde la edición; el colaborador sigue sin poder; y la portada se ve chica y dentro del cuadro.
