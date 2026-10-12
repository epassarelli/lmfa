# Cambio: gate-editorial-sections-by-entities (opcion C)

Estado: APROBADA por el usuario en el chat (2026-10-12: "Vayamos por el punto C") e implementada en local. Extiende `backoffice-access-by-entities`; sigue detras del flag `BACKOFFICE_ACCESS_BY_ENTITIES` (apagado por defecto).

## Decision y datos
Datos locales: Mitos (284) y Comidas (808) son 100% de un administrador; Noticias 366 (346 admin, 18 de un colaborador, 2 sin rol); de 434 colaboradores solo 1 creo contenido propio. La carga de Mitos y Comidas es del equipo; Noticias se alinea con la logica de fichas (Etapa 2).

## Reglas (con el flag activo)
| Seccion | Se habilita a |
|---|---|
| Noticias | administrador, rol `verificado`, o quien administra al menos un artista |
| Mitos y Leyendas | administrador o rol `verificado` |
| Comidas Tipicas | administrador o rol `verificado` |
- Sidebar (`can`) y rutas (`backoffice.section:<seccion>`) con el mismo mecanismo que las demas secciones.
- Rechazo web: redireccion a Mis fichas con aviso; el texto es distinto para las secciones solo por rol ("Esta seccion es para usuarios verificados y administradores"). JSON: 403 con el mismo mensaje.
- Contenido ya publicado no se toca: la restriccion alcanza lo que se puede crear o editar desde el backoffice de ahi en mas.
- Con el flag apagado nada cambia.

## Cambios tecnicos
- `config/backoffice_access.php`: cada seccion pasa a `['entities' => [...], 'roles' => [...]]` (se mantiene compatibilidad con la lista simple de tipos).
- `canAccessBackofficeSection()` acepta roles ademas de fichas.
- `EnsureBackofficeSection`: mensaje segun el tipo de seccion.
- Rutas `news`, `mitos`, `comidas` y items del menu con `can`.

## Fuera de alcance
Enciclopedia, Penas, Radios, Clasificados, Copa y demas secciones; el flujo "Contribuir" para Mitos/Comidas (alternativa D, a evaluar si aparece demanda); migraciones.

## Validacion
`BackofficeAccessByEntitiesTest` actualizado y suite completa.

## Riesgos
- Colaboradores sin fichas ni rol `verificado` pierden Noticias, Mitos y Comidas al activar el flag (en la base local casi nadie las usa). Verificar en produccion antes de activar.
