## Why

El portal está recibiendo información de intérpretes, festivales y productoras y necesita que cada uno cargue y mantenga **solo lo suyo**, sin que un colaborador pueda escribir en nombre de otro. Hoy la propiedad es débil e inconsistente (relevado en código y base local, oct-2026):

- `interpretes.user_id`, `festivales.user_id`, `penias.user_id` y `radios.user_id` son enteros con default `1` (el administrador): casi todo figura como del admin, así que no sirven para decidir quién puede editar.
- `InterpretePolicy::update` permite editar si `user_id == user` o es admin; `EventPolicy` usa `created_by`. No existe un vínculo "este usuario administra a este intérprete/festival".
- Hay 423 usuarios con rol `colaborador` y 1 con `prensa`. El seeder les da `access` a 8 entidades; la base local solo tiene `access noticia`. Seeder y base no coinciden.
- `organizations` y `organization_members` existen (rol `owner/editor/member`, estado `active/inactive/pending`) pero están vacías, y `Event` y `News` ya tienen `organization_id`. La Pasarela ya autoriza por membresía (`PublicationRequestController::authorizeContent`). Falta conectar organizaciones con intérpretes, festivales, peñas y radios.

## What Changes

- Usar `organizations` como **figura central de contacto y administración**: una organización agrupa a 1..N entidades del portal y tiene 0..N usuarios miembros. Un solista es una organización de una entidad (se crea de forma transparente, sin exigírselo al usuario).
- Nueva tabla pivote polimórfica `organization_entities` (organización ↔ intérprete/festival/peña/radio).
- Nuevas columnas en `organizations` para consentimiento de contacto, baja y verificación (cuándo y cómo).
- Autorización por pertenencia: un usuario con membresía activa `owner`/`editor` en una organización vinculada a una entidad puede editarla; el administrador conserva todo; el acceso legacy por `user_id` se mantiene como compatibilidad.
- Listados de backoffice para no administradores acotados a las entidades que administran.
- Servicio para crear la organización de una entidad y vincularla (base del futuro "reclamar perfil").

## Capabilities

### New Capabilities
- `entity-management`: vínculo organización ↔ entidades, membresías con efecto de permiso y consentimiento/verificación de contacto.

### Modified Capabilities
- Ninguna spec vigente. Cambia el comportamiento de `InterpretePolicy` y `FestivalPolicy` (ver design).

## Impact

- Una migración nueva (pivote + columnas en `organizations`), reversible. **No se ejecuta sin mostrar `migrate:status` y recibir confirmación.**
- Modelos: `Organization`, `User`, `Interprete`, `Festival`; nuevo `OrganizationEntity`, trait `HasOrganizations`, servicio de aprovisionamiento.
- Policies: `InterpretePolicy`, `FestivalPolicy`. Controladores Backend de intérpretes y festivales (scope de listado).
- Sin cambios en rutas públicas, slugs ni SEO. Sin migración de datos existentes: las entidades sin organización siguen funcionando igual.
- No se reutiliza `social_accounts` (credenciales OAuth de publicación de la Pasarela).

## Non-goals (cambios posteriores)

- Pantalla de administración de organizaciones/miembros/vínculos en el backoffice (slice 2).
- Autorización de Eventos y Noticias por organización: otro agente está modificando `EventController`, `Event`, `AuthServiceProvider` y rutas en `feature/event-programs-and-venues`; se hace después de que esa rama entre a `dev` (slice 3).
- Login con Google/Facebook, solicitud de vínculo y verificación por coincidencia de email (slice 4).
- Carga pública sin login.
- Envío masivo de correos.
- Decidir la relación con `crm-contact-directory` (ver design, pregunta abierta 1).
- Reconciliar seeder y base de permisos del rol colaborador (se documenta, no se toca).
