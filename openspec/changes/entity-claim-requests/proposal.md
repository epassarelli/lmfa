## Why

Los slices anteriores permiten que el administrador vincule usuarios con fichas, pero un artista, un festival o una productora todavía no puede **pedir** administrar su ficha: el administrador tendría que enterarse por fuera y cargarlo a mano. Para que la convocatoria escale, cualquier usuario autenticado debe poder solicitar administrar un intérprete o un festival, y el administrador aprobarlo o rechazarlo con trazabilidad.

El pedido depende solo de estar autenticado. Login común, Google y Facebook usan el mismo guard `auth` y aterrizan en el mismo `/admin` (`SocialiteController` hace `Auth::login`), así que la funcionalidad es la misma con los tres. Que Google y Facebook hoy no funcionen es un problema aparte (se investiga después).

## What Changes

- Nueva tabla `entity_claim_requests` (usuario, ficha polimórfica, relación con la ficha, mensaje, teléfono opcional, estado, revisor, nota y organización resultante).
- Sección **Mis fichas** (`admin/mis-fichas`), para cualquier usuario autenticado, con o sin rol:
  - fichas que administra, con acceso directo a editarlas;
  - formulario para solicitar administrar un intérprete o festival, con búsqueda por nombre;
  - sus solicitudes y su estado, con opción de cancelar las pendientes.
- Sección **Solicitudes de vínculo** (`admin/claims`, solo `administrador`): listado con filtro por estado, detalle con datos del usuario y de la ficha, y acciones aprobar (con rol) o rechazar (con nota).
- Al aprobar se usa la organización activa de la ficha o se crea la principal (`OrganizationProvisioner`) y se agrega al usuario como miembro activo; los permisos efectivos salen de la regla ya existente.
- Límites contra abuso: una solicitud pendiente por usuario y ficha, máximo 5 pendientes por usuario, `throttle` en el alta, y no se puede pedir una ficha que ya se administra.
- Datos demo: solicitudes pendientes de un usuario sin vínculos.

## Capabilities

### New Capabilities
- `claim-requests`: solicitud y revisión de vínculos usuario ↔ ficha.

## Impact

- Una migración reversible (una tabla nueva).
- Nuevos modelo, servicio, dos controladores, request, vistas y rutas (`routes/admin.php`, entradas en `config/adminlte.php`).
- Sin cambios en rutas públicas, SEO ni en el login. No modifica `SocialiteController`.

## Non-goals

- Reparar el login con Google y Facebook (tarea aparte).
- Notificaciones por correo al administrador o al solicitante (se ve el estado en pantalla; correo queda para después).
- Verificación automática (coincidencia de email o código) y botón público "Reclamar este perfil" en las fichas.
- Solicitudes sobre eventos, noticias, peñas o radios (el registro de tipos permite sumarlas).
