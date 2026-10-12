# Cambio: entity-member-content-access-events-news (Etapa 2)

Estado: APROBADA e implementada en local (2026-10-11). Regla multi-artista aprobada; todo va a Revision.

## Objetivo
Que quien administra un artista (miembro activo owner/editor de una organizacion activa, via `canManageInterpreteId`) pueda crear, editar y publicar directamente eventos y noticias de ese artista, sin roles ni permisos globales. Criterio del usuario: aprobar la ficha implica que carga contenido directamente.

## Problema actual (confirmado en codigo)
- `EventPolicy`: create requiere rol administrador/prensa/colaborador; update/view solo administrador o `created_by`.
- `NewsPolicy`: viewAny requiere `read noticia`; create `create noticia`; update `update noticia` o `created_by`. `NoticiaPolicy` (paralela) solo por permisos.
- Publicacion: `EventService` y `NewsService` fuerzan `editorial_status = draft` si `auth()->user()->canPublish()` es falso (permiso global `publish contents`). Un miembro quedaria siempre en borrador.
- Jornadas: `EventProgramController` exige rol/`create` de Event y creador o administrador.
- Multi-artista: Event usa pivote `event_interprete` (`interprete_ids`) y `event_festival`; News usa `interprete_principal_id` + `interprete_secundarios` (pivote `interprete_noticia`).
- Listados: Eventos filtra por `created_by` solo para colaborador/prensa; Noticias filtra por `created_by` para quienes no son administrador.

## Regla multi-artista propuesta (DECISION PENDIENTE, ver abajo)
- **Crear:** el registro debe incluir al menos un artista que el usuario administra. Puede sumar otros artistas como invitados.
- **Editar/publicar:** permitido si es el creador (`created_by`), o si administra TODOS los artistas vinculados (y hay al menos uno). Un evento/noticia con artistas ajenos creado por otro (p. ej. una grilla de festival) sigue siendo del administrador o de su creador.
- **Al editar** debe seguir incluyendo al menos un artista propio (no puede "soltar" el registro quitando a todos los suyos hacia un tercero).
- **Publicar:** `editorial_status = published` directo para estos casos; para el resto de los usuarios no cambia.

## Alcance incluido
- `EventPolicy` y `NewsPolicy` (+ `NoticiaPolicy` si sus rutas siguen activas): create/view/update/viewAny segun la regla.
- Publicacion contextual: reemplazar `canPublish()` global por una verificacion que considere los artistas del registro (`canPublishFor(artistIds)`), en `EventService`, `EventProgramService` y `NewsService`.
- Validacion server-side de `interprete_ids` (eventos) e `interprete_principal_id`/`interprete_secundarios` (noticias) segun la regla.
- Listados y selectores: Eventos y Noticias limitados a lo propio/de sus artistas para quien no tiene permiso global; selector de artistas: propios mas invitados (busqueda, sin listar todo si hay muchos).
- Jornadas (`EventProgramController`): mismo criterio que el evento.
- Tests Feature `MemberEventsNewsAccessTest`.

## Fuera de alcance
- `delete` (sigue solo administrador).
- Eventos de festivales (`event_festival`) y administracion por festival (Etapa 3).
- Vinculos a `venue`, `knowledge_articles`, `penia_profiles` (se mantienen como estan; ver riesgos).
- Migraciones o esquema.
- Cambiar roles/permisos globales o el flujo de solicitudes.

## Archivos afectados (estimados)
- app/Policies/EventPolicy.php, NewsPolicy.php, NoticiaPolicy.php
- app/Traits/ManagesOrganizationEntities.php (helpers multi-artista, `canPublishFor`)
- app/Services/EventService.php, EventProgramService.php, NewsService.php
- app/Http/Requests/ShowRequest.php, NoticiaRequest.php
- app/Http/Controllers/Backend/EventController.php, EventProgramController.php, NewsController.php
- tests/Feature/EntityManagement/MemberEventsNewsAccessTest.php

## Reglas funcionales
1. Editor sin roles crea evento con su artista (y un invitado) -> se crea publicado.
2. Intenta crear evento sin ningun artista propio -> validacion rechaza.
3. Edita evento creado por el con invitados -> permitido; evento de otro con solo artistas propios -> permitido; evento de otro con un artista ajeno -> 403.
4. Rol `member`, membresia inactiva o pendiente, organizacion inactiva -> sin acceso.
5. Noticias: igual criterio con principal + secundarios.
6. Jornadas: crear/editar jornadas de un evento que puede editar.
7. Listado muestra solo lo propio o de sus artistas, sin N+1.
8. Administrador, colaborador y prensa sin cambios.

## Reglas tecnicas
- Reutilizar `managedInterpreteIds()` (memoizado) y subconsultas; sin consultas por fila.
- Tests con `DatabaseTransactions`; sin migraciones; URLs publicas intactas.
- La publicacion directa no debe saltear la validacion editorial existente (slugs, imagen, etc.).

## Riesgos
- **Autorizacion ampliada con varios artistas**: un error expone eventos ajenos. Mitigacion: tests negativos obligatorios (caso 3).
- **Contenido publico directo**: noticias y eventos de miembros salen sin revision previa (decision del usuario). El administrador puede despublicar desde el Centro de Moderacion.
- **Vinculos a venues/articulos/penas**: un miembro podria asociar su evento a fichas ajenas; hoy cualquier creador puede. No se amplia ni se restringe en esta etapa.
- Servicios con `canPublish()` global usados desde API/automatizaciones: el cambio debe ser aditivo (global OR contextual).

## Validacion
Tests de las reglas 1-8, regresion de `tests/Feature/Events`, `News`, `Music`, `EntityManagement`; prueba manual con cuenta sin roles.

## Decisiones que necesito del usuario
1. **Regla multi-artista**: ¿se aprueba la propuesta (crear con al menos un artista propio; editar si es creador o administra todos los vinculados)? Alternativa permisiva: editar si administra al menos uno de los vinculados (riesgo: pisar grillas de otros). Alternativa estricta: solo lo que el mismo creo.
2. **Noticias**: ¿puede un miembro publicar noticias sobre su artista directamente (mismo criterio que eventos)? Se asume que si.

## Criterio de aceptacion
Un editor de un artista, sin roles globales, crea, edita y publica eventos y noticias que involucran a su artista segun la regla, no puede tocar los de terceros, y nada cambia para los demas roles.

## Aprobacion
Pendiente.

## RECTIFICACION del usuario (2026-10-11)
- Regla multi-artista: **aprobada** tal como esta propuesta (crear con al menos un artista propio; editar si es creador o administra todos los vinculados).
- **Publicacion:** en esta primera instancia todo va a Revision. Se elimina la "publicacion contextual" (`canPublishFor`): el miembro guarda en `draft` / `estado = 0` y un administrador publica. Se mantiene `canPublish()` global sin cambios, lo que simplifica EventService/EventProgramService/NewsService (no se tocan).
- Las decisiones 1 y 2 de la seccion anterior quedan resueltas (2: noticias tambien van a Revision).
- Pendiente de implementar; depende en parte de `backoffice-access-by-entities` (visibilidad de secciones).

## Resultado (implementado en local, 2026-10-11)
- `EventPolicy`/`NewsPolicy`: create si administra al menos un artista; view/update si es creador (o permiso global previo) o administra TODOS los artistas vinculados (>= 1). Jornadas: `EventPolicy::updateProgram` (administrador, creador o todos los eventos editables).
- Validacion server-side (`ShowRequest`, `NoticiaRequest`): si el acceso viene solo de la membresia, el registro debe incluir al menos un artista propio (puede sumar invitados).
- Todo a revision: tope en `EventService` y `NewsService`: quien no puede publicar (`canPublish()`) nunca publica, aunque el request traiga `estado=1` (en noticias tambien fuerza `estado=0`). Esto endurece tambien a colaborador/prensa (antes solo la UI lo impedia).
- Listados de Eventos y Noticias: para no administradores, solo lo propio o editable por membresia (antes Eventos solo restringia a colaborador/prensa).
- Tests: `MemberEventsNewsAccessTest` (12 casos). Suite completa: 496 pasan; 13 fallos previos por datos reales.
- Fuera de alcance / pendiente: Etapa 3 (festivales), invitados que sumen un artista ajeno a un evento existente bloquean la edicion futura salvo para el creador (consecuencia de la regla).
