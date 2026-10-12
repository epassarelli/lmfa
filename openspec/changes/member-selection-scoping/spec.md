# Cambio: member-selection-scoping

Estado: APROBADA por el usuario en el chat (2026-10-13: "1. Si, 2. Solos suyos. 3. Si, iguales"). Implementada en local, sin migraciones. **Rige solo con `BACKOFFICE_ACCESS_BY_ENTITIES` activo**; con el flag apagado todo queda como esta.

## Problema (medido)
Una cuenta colaborador que administra un artista, con el flag encendido, podia: ofrecer y crear Discos/Canciones de **cualquier** artista; ver todos los Intérpretes en su listado; crear Noticias sin ningun artista propio o con un artista ajeno como principal; y crear Eventos sin artistas ni festivales propios. Causa: el permiso global `create album/cancion/noticia` del rol colaborador ganaba sobre las reglas de fichas.

## Reglas (flag activo, usuario no administrador)
1. **Discos y Canciones:** el selector muestra **solo los artistas que administra** (ni siquiera se ofrecen los demas) y el servidor lo exige (alta, edicion y alta rapida de canciones). Un registro propio previo se puede seguir editando mientras no cambie de artista.
2. **Noticias:** el **artista principal es obligatorio y tiene que ser uno que administra** (selector limitado); los **secundarios** pueden ser cualquier artista (invitados). Mismo criterio de excepcion para una noticia propia previa cuyo principal no cambia.
3. **Eventos:** debe incluir al menos **un artista o un festival que administra** (tambien en eventos de varias jornadas: cuentan los artistas de las jornadas); los demas son invitados. El selector de artistas muestra primero "Mis artistas". Los festivales siguen la misma logica (cuenta como propio uno que administra; hay validacion y el evento queda bajo el festival).
4. **Listados:** Intérpretes se limita a las fichas propias; Discos, Canciones, Festivales, Eventos y Noticias ya mostraban solo lo propio (decision del usuario: sin modo "solo lectura").
- El administrador no cambia. Los invitados siguen pudiendo elegirse entre todos los artistas (secundarios de noticias y artistas de eventos).

## Fuera de alcance
Restringir el alta directa de artistas/festivales nuevos (hoy el permiso `create interprete/festival` sigue permitiendolo: observacion), orden "mis festivales primero" en el selector de festivales (la validacion si aplica), cambios con el flag apagado.

## Archivos
`app/Traits/ManagesOrganizationEntities.php`, `app/Http/Requests/Concerns/ValidatesManagedInterprete.php`, `ShowRequest`, `NoticiaRequest`, `InterpreteController`, `NewsController`, `EventController`, vistas `news/form` y `events/form`, `tests/Feature/EntityManagement/MemberSelectionScopingTest.php`.

## Validacion
`MemberSelectionScopingTest` (flag activo y apagado) y suite completa; los tests de las Etapas 1 a 3 siguen en verde.

## Resultado (implementado en local, 2026-10-13)
- `MemberSelectionScopingTest` (12 casos, flag activo y apagado); suite en base limpia 649 pasan, 0 fallan; en `mfa` solo los 13 previos.
- Detalle: el selector de edicion conserva el artista actual de un registro propio previo; el principal de una noticia propia previa tampoco se bloquea si no cambia.
- Observacion abierta: el permiso `create interprete/festival` sigue permitiendo el alta directa de fichas a un colaborador con ficha (saltea la solicitud de alta).
