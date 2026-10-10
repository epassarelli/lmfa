## Why

La cartelera usa la fecha inicial y no representa grillas distintas por jornada. La ubicación se repite como texto y el editor de Eventos depende de Summernote. El usuario aprobó implementar el diseño y un ABM independiente de Salas/Lugares el 2026-10-08, en una rama nueva.

## What Changes

- Conjuntos/ediciones con datos comunes y jornadas Event independientes, cargadas juntas; página pública del conjunto y fichas de jornadas con URLs existentes preservadas.
- ABM administrativo de Salas/Lugares sobre Venue, selección y alta desde Eventos; propuestas de lugares revisadas en Bandeja.
- CKEditor compartido en alta/edición; reglas comunes en Backoffice, API y moderación.
- Agenda por jornadas, incluyendo eventos continuos en curso sin inventar programación diaria.
- Sin mapas interactivos, rediseño compacto, recurrencias, migración automática del contenido histórico ni despliegue.

## Capabilities

### New Capabilities
- `event-programs`: conjuntos, jornadas, publicación, navegación y carga común.
- `event-venues`: catálogo de lugares, permisos, duplicados y ubicación consistente.
- `event-editor`: edición enriquecida y validación común de ingresos editoriales.

### Modified Capabilities
Ninguna spec principal existente se modifica; se extiende la ficha aprobada en event-detail-navigation-and-relations.

## Impact

Models Event/EventProgram/Venue; EventService y nuevos servicios; Requests web/API; controladores de Eventos, Bandeja, colaboraciones y conjuntos/lugares; rutas admin/públicas, menú AdminLTE, vistas Blade, resolver de imágenes y sitemap. Migración aditiva: event_programs y vínculo nullable en events. Tests Feature con DatabaseTransactions, sin resets. La ejecución de migraciones requiere confirmación separada conforme AGENTS.md.
## Ampliación aprobada: ingreso único (2026-10-08)
Pedido explícito del usuario: cargar un evento y gestionar sus jornadas desde Eventos. Se agrega capacidad `unified-event-entry`; elimina el acceso independiente Conjuntos / Jornadas del sidebar, unifica formulario y listado, renombra textos a Evento con jornadas y permite incorporar jornadas a un evento existente. Archivos afectados: EventController, EventProgramController, ShowRequest, EventProgramService, rutas admin, config AdminLTE, vistas de eventos/programaciones, tests Feature y documentación. Sin esquema ni actualización masiva de datos. Autorización explícita en el chat.
