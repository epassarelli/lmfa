# Cambio: entity-member-content-access-festival-events (Etapa 3)

Estado: APROBADA por el usuario en el chat (2026-10-12: "continua con la etapa 3") e implementada en local. Continua las Etapas 1 y 2.

## Objetivo
Que quien administra un **festival** (miembro activo owner/editor de una organizacion activa vinculada a la ficha) cree y edite los eventos de ese festival, con todo a revision.

## Regla (extiende la de la Etapa 2)
Un evento tiene artistas (`event_interprete`) y festivales (`event_festival`). El festival es el "contenedor" del evento; los artistas son participantes.
- **Crear:** el evento debe incluir al menos un artista **o** un festival que el usuario administra. Puede sumar otros como invitados.
- **Editar (no creador):**
  - Si el evento tiene festivales: el usuario debe administrar **todos** los festivales vinculados (los artistas de la grilla se ignoran: un organizador edita su programacion aunque incluya artistas ajenos).
  - Si no tiene festivales: regla de la Etapa 2 (administra **todos** los artistas vinculados).
- El creador siempre edita lo suyo. El administrador, todo.
- Consecuencia buscada: un evento enlazado a un festival ajeno deja de ser editable por el administrador de un artista (lo controla el festival).
- Todo lo que carga un miembro va a **revision** (sin cambios: solo publica `canPublish()`).

## Alcance incluido
- `EventPolicy` (create, view, update) y jornadas (`updateProgram`, ya apoyada en `update`).
- Validacion en `ShowRequest`: acceso solo por membresia exige al menos un artista o festival propio.
- Listado de Eventos: scope `editableByMember` con la regla extendida.
- Helpers en `ManagesOrganizationEntities` (ids de festivales administrados, regla combinada).
- Sidebar: sin cambios (Eventos ya se habilita con ficha de festival).

## Fuera de alcance
Noticias vinculadas a festivales (`festival_news`), edicion de la ficha del festival (ya existe via `canManage`), contenido de enciclopedia/penas del festival, migraciones.

## Validacion
`MemberFestivalEventsTest` y suite completa (incluye `MemberEventsNewsAccessTest` de la Etapa 2).

## Riesgos
- Cambia el alcance de la Etapa 2 para eventos con festival ajeno (mas restrictivo). Documentado en la regla.
- Linkear un evento a un festival ajeno es posible (invitado) pero queda en revision y el creador conserva acceso.
