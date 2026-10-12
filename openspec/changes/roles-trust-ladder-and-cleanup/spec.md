# Cambio: roles-trust-ladder-and-cleanup

Estado: APROBADA por el usuario en el chat (2026-10-12): eliminar el rol global `prensa`, crear el camino de confianza para publicar y corregir los hallazgos de la comparacion de roles. Migracion preparada, a ejecutar por el usuario.

## Contexto y decisiones del usuario
- "Prensa" se pensó como **la prensa de un artista**: eso ya es una relacion con una ficha (opcion "prensa" al solicitar vinculo + membresia), no un rol global. Los 2 usuarios con rol prensa son de prueba y nunca se usaron.
- Camino de confianza: alguien de confianza publica sin pasar por un administrador.

## Hallazgos que se corrigen
1. **`publish contents` no existe** en la base: `canPublish()` solo era true para administrador y varios controladores ni lo consultaban (Albumes, Canciones, Interpretes y Festivales fijaban "solo administrador").
2. **Prensa editaba contenido ajeno**: `update album/cancion/noticia` se evaluaba sin ownership (el listado ocultaba lo ajeno, la URL no).
3. **Cuatro mecanismos mezclados** (lista fija de roles, permisos por nombre, creador, membresia). Se documenta el modelo y se elimina la lista con `prensa` de 18 archivos.
4. **`isAdmin()` aceptaba `admin`** ademas de `administrador` (el rol `admin` no existe).
5. **Adicionales detectados:** `UserPolicy::viewAny` dejaba a prensa listar usuarios (datos personales); `FestivalController` tomaba `status` del request (un colaborador podia publicar mandando `status=published`); Album/Cancion sobreescribian `user_id` en cada edicion (el ultimo editor pasaba a ser el "dueño").

## Alcance incluido
- **Migracion** (revisable, la ejecuta el usuario): crea el permiso `publish contents` y el rol `verificado` (solo ese permiso); reasigna a colaborador a los usuarios con rol `prensa` y elimina el rol. `down()` recrea el rol `prensa` con sus permisos originales (no reasigna usuarios).
- **Camino de confianza:** el administrador asigna el rol `verificado` *ademas de* `colaborador` (desde Usuarios). `canPublish()` pasa a ser true; todo lo que ese usuario puede escribir (lo propio o de fichas que administra) se publica directo: Discos, Canciones, Eventos (y jornadas), Noticias, y las fichas de Interprete y Festival. Quien no tiene el permiso sigue yendo a revision.
- Controladores: `estado/status` segun `canPublish()` en Album, Cancion, Interprete y Festival (el status de Festival ya no lo decide el request).
- Policies: `update` de Album, Cancion y Noticia = administrador, creador o miembro de la ficha del artista. Los permisos globales `update ...` dejan de habilitar edicion cruzada. Album/Cancion conservan `user_id` al editar. El creador puede editar lo propio aunque no administre ese artista.
- Limpieza: sin literal `prensa` en `app/`; `isAdmin()` = rol `administrador`; `UserPolicy::viewAny` solo administrador.
- Documentacion: `project/docs/permisos_y_roles.md`.

## Fuera de alcance
- Mitos, Comidas, Peñas y Radios en la escalera de confianza (siguen revision; se trata con el pendiente de colaboradores).
- Permitir `verificado` sin `colaborador` (se documenta que es aditivo).
- Delegar aprobaciones al owner (pendiente aparte).
- Cambiar nombres de permisos de la base.

## Reglas funcionales
1. Colaborador sin `verificado`: todo a revision (sin cambios).
2. Colaborador + `verificado`: lo propio o de sus fichas se publica directo.
3. `verificado` no habilita editar contenido ajeno ni de artistas que no administra.
4. Usuario con permiso directo `update album` pero ajeno al disco: 403.
5. Colaborador edita el disco que el mismo creo (aunque no administre el artista): permitido, `user_id` intacto.
6. Un colaborador no puede publicar un Festival mandando `status=published`.
7. Nadie salvo administrador lista usuarios.
8. Administrador sin cambios.

## Archivos afectados (principales)
database/migrations/2026_10_12_100000_*; app/Models/User.php, app/Traits/ManagesOrganizationEntities.php (no cambia); Controllers Album/Cancion/Interprete/Festival/Comida/Mito; Policies Album/Cancion/News/User y las 8 que listaban `prensa`; ShowRequest/NoticiaRequest/ValidatesManagedInterprete; tests/Feature/EntityManagement/TrustedPublishingTest.php.

## Validacion
Tests de las reglas 1-8; suite completa; la migracion se verifica en local tras ejecutarla (`migrate:status`, roles y permisos resultantes).

## Riesgos
- Eliminar un rol es una operacion sobre datos: por eso es migracion revisable, reversible en estructura (no en asignaciones) y con reasignacion previa a colaborador.
- Quitar la edicion cruzada puede sorprender si alguien dependia de ella (solo tenia el rol prensa, sin uso real).
- Verificado publica sin revision: el administrador controla a quien se lo asigna.

## Aprobacion
Pedido del usuario en el chat (2026-10-12).
