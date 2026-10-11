# Cambio: entity-member-content-access

Estado: APROBADA 2026-10-11. Etapa 1 (Discos y Canciones) implementada en local en dev. Etapas 2 y 3 pendientes.

## Objetivo
Que un usuario que administra un Interprete (miembro activo owner/editor de una organizacion activa vinculada a la ficha, via `canManage`) pueda gestionar el contenido propio de ese artista (discos, canciones, luego eventos y noticias) sin necesitar roles ni permisos globales (`create album`, `update cancion`, rol prensa/colaborador).

## Problema actual
`canManage` solo gobierna `InterpretePolicy` y `FestivalPolicy`. `AlbumPolicy`, `CancionPolicy`, `EventPolicy` y `NewsPolicy` dependen de permisos/roles globales o de `created_by`. Un artista aprobado edita su ficha pero no puede cargar su disco ni su fecha. Album, Cancion y News tienen `interprete_id`; Event se vincula por pivote `event_interprete` (N artistas) y `event_festival`.

## Division propuesta (cada etapa es entregable por separado)
1. **Etapa 1 - Discos y Canciones** (relacion 1 artista por registro; la mas simple y segura).
2. **Etapa 2 - Noticias y Eventos** (hay N artistas por registro; requiere decidir regla multi-artista).
3. **Etapa 3 - Contenido de Festivales** (eventos de `event_festival`), si se quiere.

## Alcance incluido (Etapa 1)
- `AlbumPolicy` y `CancionPolicy`: `update` permitido si el usuario `canManage` el interprete del registro (ademas de lo actual). `create` permitido si administra al menos un Interprete (ademas de lo actual).
- Al crear/editar, `interprete_id` solo puede ser un artista que el usuario administra (validacion server-side; un administrador global conserva acceso total).
- Listados de Discos y Canciones para miembros sin rol global: solo registros de sus artistas (y los propios por `user_id`).
- Sidebar: Discos y Canciones visibles para quien administra al menos un artista.
- Helper en `ManagesOrganizationEntities` (p. ej. `managesAny(string $entityType)`), memoizado.

## Fuera de alcance
- `delete` (sigue solo administrador).
- Cambiar roles/permisos globales, el flujo de aprobacion de solicitudes o el rol `member` (sigue solo lectura).
- Contenido de otros artistas, eventos, noticias y festivales (etapas 2 y 3).
- Migraciones o cambios de esquema.
- Publicacion automatica de letras (ver decisiones).

## Archivos afectados (Etapa 1)
- app/Policies/AlbumPolicy.php, app/Policies/CancionPolicy.php
- app/Traits/ManagesOrganizationEntities.php
- app/Http/Controllers/Backend/AlbumController.php, CancionController.php (scope de index; validacion de interprete_id en store/update)
- config/adminlte.php (condicion de visibilidad)
- tests/Feature/EntityManagement/MemberContentAccessTest.php

## Reglas funcionales
### Caso 1 - Editor edita disco de su artista
Dado un usuario sin roles, editor activo de la organizacion del Interprete A
Cuando edita un disco con interprete_id = A
Entonces puede hacerlo.

### Caso 2 - Disco de otro artista
Dado el mismo usuario
Cuando intenta editar un disco de un Interprete B que no administra
Entonces recibe 403.

### Caso 3 - Crear solo para sus artistas
Dado el mismo usuario
Cuando crea un disco con interprete_id = B
Entonces la validacion lo rechaza; con A se crea.

### Caso 4 - Rol member
Dado un miembro con rol `member` (solo lectura) o con membresia inactiva / organizacion inactiva
Entonces no puede crear ni editar.

### Caso 5 - Listado
Dado un usuario sin rol global que administra A
Cuando abre Discos
Entonces ve solo discos de A (y los propios), sin N+1.

### Caso 6 - Sin regresion
Administrador, colaborador y prensa conservan exactamente el acceso actual.

## Decisiones que necesito del usuario
1. **Letras (derechos).** AGENTS.md exige decision humana antes de ampliar la publicacion de letras completas. Opciones: (a) los miembros no pueden cargar/editar el campo `letra` (recomendado para Etapa 1); (b) pueden, pero la cancion queda en revision hasta que un administrador la publique; (c) pueden publicarla directamente.
2. **Publicacion.** Lo que carga un miembro, ¿se publica directo o queda pendiente de revision en el Centro de Moderacion? Recomendado: pendiente (calidad editorial). Falta verificar si Album/Cancion tienen estado editorial hoy.
3. **Eventos/Noticias con varios artistas (Etapa 2).** Opciones: administra al menos uno de los artistas vinculados / administra todos / solo los que creo. Recomendado: solo crear eventos que incluyan a su artista y editar los que creo o donde es el unico artista.
4. **Rama de trabajo.** AGENTS.md prohibe crear ramas sin autorizacion: ¿se trabaja en `dev` o en una rama nueva?

## Reglas tecnicas
- Reutilizar `managedEntityIds` (memoizado) y `managedEntityIdsSubquery`; sin consultas por fila en listados.
- Tests Feature con `DatabaseTransactions`; sin migraciones.
- No cambiar rutas ni URLs publicas.

## Riesgos
- Ampliar autorizacion: un error expone contenido de terceros. Mitigacion: tests negativos (Casos 2, 3, 4) obligatorios.
- Interaccion con rol colaborador/prensa que hoy filtra por `user_id`: combinar con OR, no reemplazar.

## Validacion
Tests de los Casos 1-6, regresion `tests/Feature/Music` y `EntityManagement`, y prueba manual del usuario con una cuenta sin roles.

## Criterio de aceptacion
Un editor de un artista, sin roles globales, carga y edita discos y canciones de ese artista y de ningun otro, y nada cambia para los demas roles.

## Aprobacion
Pendiente. El usuario eligio la Opcion A en el chat (2026-10-11); falta aprobar esta spec y las decisiones 1-4.

## Decisiones del usuario (2026-10-11)
1. Letras: **opcion C** - el miembro puede cargar/editar `letra` y publicarla directamente (decision humana sobre derechos: aprobar la ficha implica confianza para cargar contenido). `rights_status` y `lyrics_source_url` siguen disponibles en el formulario.
2. Publicacion: **carga directa** (`estado = 1` si administra el artista del registro; el resto de los casos conserva `estado = 0`).
3. Multi-artista (Etapa 2): criterio general "si se le aprobo la ficha, carga contenido directamente"; el detalle de la regla se define al especificar la Etapa 2.
4. Rama: se trabaja en `dev`.

## Resultado Etapa 1 (implementado en local)
- Sidebar: sin cambios (Discos y Canciones ya se muestran sin condicion `can`).
- Policies Album/Cancion: `view`/`update` por `canManageInterpreteId`; `create` si administra al menos un artista.
- `interprete_id` validado en AlbumRequest/CancionRequest (trait `ValidatesManagedInterprete`) solo para quien no tiene permiso global.
- Listados y selector de artistas limitados a los artistas administrados (scope `ownedOrOfManagedInterprete`).
- Tests: `tests/Feature/EntityManagement/MemberContentAccessTest.php` (9 casos). Suite completa: 465 pasan; 13 fallos previos por datos reales de la base local.
- Observacion fuera de alcance: `CancionController@storeAjax` (`canciones/store-ajax`) queda fuera de `authorizeResource` y crea canciones activas para cualquier usuario autenticado.
