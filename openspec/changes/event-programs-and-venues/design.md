## Context
Laravel 10, Blade, AdminLTE y CKEditor local existentes. Event es la unidad pública y de artistas. Venue existe pero no tiene ABM. Bandeja (ModerationController) publica Event directamente; colaboraciones tampoco usan EventService. Rama autorizada feature/event-programs-and-venues, con cambios anteriores de guía preservados.

## Goals / Non-Goals
**Goals:** carga común de una edición y múltiples jornadas; catálogo independiente; reglas coherentes, URLs estables y revisión humana.
**Non-Goals:** mapas interactivos, recurrencias, layout laptop, agrupación automática de eventos históricos, SQL sobre producción.

## Decisions
- EventProgram contiene título, slug, cuerpo, resumen, lugar y entradas comunes, propietario y estado editorial. Event.program_id nullable permite conservar cada jornada y todas sus relaciones actuales. No se reutiliza Festival porque es una ficha permanente, no una edición.
- Hasta 60 jornadas explícitas por conjunto. Crear un conjunto carga jornadas juntas; agregar y editar se hace como Event vinculado. Artistas, fecha, título y fin son siempre propios. Fin opcional; cruzar medianoche no genera jornadas. La agenda de una fecha usa intersección de intervalos para continuos, sin repetir tarjetas en el listado general.
- Defaults comunes se materializan en Event para conservar búsquedas/indexación. program_overrides JSON registra excepciones; edición de campos comunes propaga solo a jornadas que heredan. No propaga estados editoriales automáticamente: publicar una jornada no publica sus hermanas; publicar el conjunto solo habilita su página. La página requiere conjunto público y al menos una jornada pública.
- Al corregir defaults como administrador se preservan estados de las jornadas; los cambios de un editor sin permiso de publicación vuelven a borrador para revisión, también en las jornadas afectadas. Se respeta canPublish existente. La imagen común no duplica archivos.
- Imagen común pertenece a EventProgram (HasMedia); fallback desde Event con relación eager-loaded. Imagen propia gana. No duplicar archivos por jornada.
- Seleccionar Venue materializa ubicación en Event; correcciones de catálogo actualizan ubicación de eventos vinculados, incluidas jornadas, sin cambiar su sede. EventProgram conserva venue_id como default; modificar sede se hace desde jornada o conjunto. Campos de ubicación del catálogo se muestran como datos de consulta. Históricos sin venue siguen funcionando. Propuestas nuevas se guardan pending y requieren revisión; catálogo admin permite activarlas. Baja de lugares con usos se bloquea, ofreciendo inactivar.
- ABM de lugares solo administrador; colaboradores/prensa seleccionan activos y proponen nuevos. Slugs únicos estables; posibles duplicados por nombre/localidad/provincia/dirección se rechazan con mensaje revisable. No geocoding ni servicios nuevos.
- Moderación y colaboraciones usan EventService / EventProgramService. Inputs anidados tienen validación explícita; permisos de propietario, publicaciones y asociación a conjuntos se validan en servidor. No habilitar estado published por payload no autorizado.
- Página /programacion-de-eventos/{slug}, canonical propio; jornadas mantienen URLs. Conjunto enlaza jornadas paginadas públicas, con artistas propios y sin inferir grilla de Festival. Publicaciones futuras y cancelados no visibles. Sitemap incluye conjuntos públicos con jornadas visibles.
- CKEditor local compartido, H2/H3 sin H1 en cuerpo. JS solo en formularios; no dependencias nuevas.

## Risks / Trade-offs
- Cambios comunes afectan varias jornadas → herencia explícita y aviso en formulario; preservar excepciones.
- Duplicados históricos → sin conversión automática; propuestas con revisión.
- La base local es persistente → migración aditiva revisable, migrate:status y confirmación antes de aplicar; tests transaccionales sin DDL ni resets, Queue/Http fake.
- Correcciones de catálogo afectan históricos vinculados → describirlo en ABM; los registros legacy no se vinculan automáticamente.
- Performance → eager loading de conjunto/imagen/lugar en tarjetas, paginación SQL y sin mapa/JS público adicional. No afirmar mediciones productivas.

## Migration Plan
Crear event_programs y agregar program_id nullable y program_overrides JSON nullable en events. No modificar registros existentes. Revisar migrate:status, solicitar confirmación y ejecutar solo migración nueva como www-data. Rollback de código antes de quitar columnas; rollback de esquema pierde agrupaciones nuevas y requiere autorización, no se ejecuta automáticamente.

## Open Questions
Sin bloqueos funcionales. El usuario autorizó explícitamente la migración local el 2026-10-08; aplicada y validada con pruebas transaccionales. Revisión interactiva por el usuario pendiente; publicación y despliegue fuera de alcance.
## Ingreso único aprobado (2026-10-08)
Se conserva el modelo EventProgram/Event y las URLs públicas. Nuevo evento usa los campos comunes existentes, un selector de formato y un bloque de jornadas que deshabilita campos de fecha/artistas únicos cuando corresponde. Validación condicional en ShowRequest y despacho al servicio actual; al editar jornada se oculta el selector técnico de conjunto y se enlaza al evento principal. Listado paginado selecciona un Event representante (mínimo ID por program_id) con eager loading y agregado de cantidad/fechas, evitando filas repetidas; búsqueda incluye programación y artistas de jornadas. Conversión explícita crea defaults, conserva imagen y vínculos del Event original sin copiar medios, dentro de transacción y con policy update; la imagen común del evento se puede cargar desde su edición. Rutas anteriores siguen compatibles; índice anterior redirige al único listado. Pruebas de permisos, errores, conservación de IDs y consultas acotadas.
