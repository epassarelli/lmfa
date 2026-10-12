# Cambio: remove-contribuir-flow

Estado: APROBADA por el usuario en el chat (2026-10-12): "quitar todo eso de contribuir ... no puede ser una alternativa paralela". Implementada en local.

## Decision
El flujo publico "Publicar / Contribuir" (`/admin/contribuir`) desaparece. Hay una sola puerta: **Publicar → login → /admin**, y de ahi en mas rigen fichas, solicitudes de vinculo/alta, roles y revision.

## Hechos que lo justificaban (verificados en el codigo)
- Cualquier usuario autenticado podia proponer 5 tipos de contenido; el administrador los aprobaba y el sistema los creaba **publicados** o los aplicaba **directo** sobre el registro.
- Al aprobar un artista/festival/cancion nuevo, el autor quedaba como dueño por `user_id` (legacy) sin que nadie lo decidiera: duplicaba y contradecia el circuito de fichas.
- Los puntos y el rango ("Colaborador Bronce") solo se otorgaban en esa aprobacion.
- Uso real: 1 contribucion pendiente en la base local.

## Alcance incluido
- Se eliminan: rutas `admin/contribuir/*` y `admin/contributions/*`; `Frontend\ContributionController` y `Backend\ContributionController`; vistas `frontend/contributions/*` y `backend/contributions/*`; el modelo `Contribution` y `User::contributions()`; el contador "Publicaciones Pendientes" del dashboard; los 4 enlaces publicos "Sugerir correccion..." (cancion, festival, interprete, evento); los tests del flujo.
- "Publicar" (navbar) lleva a `/admin`: un visitante pasa por el login y vuelve a `/admin`; quien ya inicio sesion entra directo.
- Con `BACKOFFICE_ACCESS_BY_ENTITIES` activo, un usuario que no es administrador que entra a `/admin` va a Mis fichas (el dashboard muestra contadores globales y enlaces a secciones bloqueadas). Con el flag apagado el dashboard no cambia.

## Datos
- **No se borra ni se migra nada**: la tabla `contributions` y las columnas `users.points`/`users.rank` se conservan. Sin codigo que las use. Un retiro definitivo seria una migracion aparte, a aprobar, despues de exportar la tabla.
- Antes de desplegar en produccion: resolver (aprobar/rechazar) las contribuciones pendientes con la pantalla actual y exportar la tabla (ver checklist).

## Fuera de alcance
Reemplazar los enlaces publicos por "¿Sos este artista? Solicita administrar tu ficha" (propuesta aparte), retirar puntos/rango, migracion de borrado de la tabla.

## Validacion
`ContributionFlowRemovedTest` y suite completa; se retiran los tests que ejercitaban el flujo (`EventEditorialContributionTest`, un caso de `BackendListingDiscoveryTest`, dos de `UnifiedNewsFlowTest`).

## Riesgos
- Se pierde la posibilidad de que el publico sugiera correcciones sin ficha (decision del usuario). URLs viejas `/admin/contribuir` dejan de existir (404; area privada).
- Contribuciones pendientes en produccion quedarian sin pantalla de revision: resolverlas antes del despliegue.

## Actualizacion (2026-10-12, mismo dia)
El usuario confirmo que **no hay contribuciones en produccion** y pidio quitar todo. Se agrega:
- Migracion `2026_10_12_140000_drop_contributions_and_user_points_rank`: elimina la tabla `contributions` y `users.points`/`users.rank` (nunca se otorgaron puntos: 0 usuarios con puntos en local; 1 fila de prueba de contribucion, respaldada fuera del repo antes de migrar). `down()` recrea la estructura, no los datos. Aplicada en `mfa` y `mfa_test`.
- Se retiran `User::$fillable` points/rank y `getRankAttribute`.
- Enlace publico "¿Sos este artista o lo representas? / ¿Organizas este festival? Solicita administrar su ficha" (home y biografia del artista, ficha del festival) hacia Mis fichas con la ficha preseleccionada, via login. `rel="nofollow"` y `data-nosnippet`; no aparece en evento ni cancion.
- Decision sobre puntos/rango como insumo de la escalera de permisos: no se conservan. Si hace falta una señal de merito para promover a `verificado`, se calcula bajo demanda desde el contenido aprobado, sin almacenarla.
