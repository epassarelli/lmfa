## Why

El slice 1 (`organization-entity-management`) creó el modelo y la regla de permisos, pero hoy no hay forma de crear organizaciones, vincularlas a fichas ni aprobar miembros sin tocar la base. Sin esta pantalla el administrador no puede operar el flujo de reclamo de perfiles ni la convocatoria a intérpretes y festivales.

## What Changes

- Sección **Organizaciones** en el backoffice, solo para el rol `administrador`, en `admin/organizations`:
  - Listado paginado en servidor con búsqueda (nombre, slug, email), filtros (tipo, estado, verificada, contactable) y orden.
  - Alta y edición: datos de contacto (email, teléfono, web, provincia, ciudad, dirección), tipo, estado, verificación (método, fecha, quién), consentimiento de contacto y baja.
  - **Fichas vinculadas**: ver, agregar (Select2 con búsqueda remota de intérpretes y festivales) y quitar vínculos; marcar principal.
  - **Miembros**: buscar usuario por nombre o email (Select2 remoto), asignar rol (`owner`, `editor`, `member`) y estado (`active`, `pending`, `inactive`); cambiar ambos y quitar. Aprobar una solicitud equivale a pasar `pending` a `active`.
- Las organizaciones **no se borran**: se desactivan (`status = inactive`), lo que revoca de inmediato los permisos que otorgaban.
- Entrada de menú en AdminLTE.
- El registro de tipos de ficha vinculables vive en `config/entity_management.php` para sumar peñas y radios después sin tocar controladores.

## Capabilities

### New Capabilities
- `organization-admin`: administración de organizaciones, vínculos y miembros en el backoffice.

## Impact

- Rutas nuevas bajo `admin/organizations` (`backend.organizations.*`) en `routes/admin.php`; entrada en `config/adminlte.php`. Otro agente modifica esos dos archivos en `feature/event-programs-and-venues`: se espera un conflicto trivial de líneas agregadas.
- Nuevos controladores, request y vistas Blade del backoffice; sin migraciones; sin cambios de modelos salvo scopes de lectura.
- Sin cambios públicos, de rutas públicas, slugs ni SEO.

## Non-goals

- Solicitud de vínculo por parte del usuario y login social (slice siguiente).
- Autorización de Eventos y Noticias por organización.
- Envío de correos y campañas.
- Borrado físico de organizaciones.
- Gestión de cuentas sociales de la Pasarela (`social_accounts`).
