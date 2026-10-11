# Cambio: backoffice-access-by-entities

Estado: BORRADOR, pendiente de decisiones y aprobacion. No implementado.

## Objetivo
Que el sidebar y el acceso por URL del backoffice dependan de las fichas (artistas, festivales) que el usuario administra:
1. Usuario registrado con rol colaborador (u otro no administrador) y **sin fichas**: ve en el sidebar solo "Mis fichas"; si entra por URL a otra seccion del backoffice, se lo rechaza con un cartel.
2. Usuario **con fichas**: ve y accede solo a las secciones que esas fichas habilitan.

## Problema actual (confirmado)
- El menu (`config/adminlte.php`) muestra casi todas las secciones sin condicion `can`; cualquiera logueado las ve.
- Las rutas de `routes/admin.php` solo tienen `auth` (y `role:administrador` en organizaciones/claims); el resto depende de policies por accion, no de "tiene fichas".
- Datos locales (lectura): 6 administradores, **432 colaboradores** (17 permisos globales: create album/cancion/comida/festival/interprete/mito/noticia/show, read de lo mismo y `access noticia`), 2 prensa, 6 sin rol; 9 organizaciones y 9 membresias. Es decir, hoy el modelo de colaborador es "carga contenido propio en borrador", no "administra una ficha".

## Que cuenta como "tener ficha"
Administrar al menos una entidad via `managedOrLegacyBy`: membresia activa owner/editor en organizacion activa, **o** `user_id` legacy del creador. Es el mismo criterio de "Mis fichas".

## Mapa propuesto de secciones (config versionada, p. ej. `config/backoffice_access.php`)
| Seccion | Se habilita si administra |
|---|---|
| Mis fichas | siempre (cualquier autenticado) |
| Interpretes | >= 1 interprete |
| Discos, Canciones | >= 1 interprete |
| Noticias | >= 1 interprete |
| Eventos / Cartelera, Salas | >= 1 interprete (Etapa 2) o festival (Etapa 3) |
| Festivales | >= 1 festival |
| Resto (Mitos, Comidas, Enciclopedia, Clasificados, Penas, Radios, Copa, Pasarela, Moderacion, Sistema) | ver decisiones |
Administrador: todo, sin cambios. Items con `can` existentes (usuarios, roles, etc.) se respetan.

## Alcance incluido
- Gates/permiso de seccion calculados con `managedEntityIds` (memoizado) y usados como `can` en el menu AdminLTE (soportado nativamente), sin filtro propio.
- Middleware de seccion en los grupos de rutas de `routes/admin.php`.
- Respuesta de rechazo (ver recomendacion).
- Tests Feature: sidebar y acceso por URL para (a) colaborador sin fichas, (b) con ficha de artista, (c) con ficha de festival, (d) con ambas, (e) administrador sin cambios.

## Fuera de alcance
- Cambiar permisos globales de roles o el flujo de solicitudes.
- Crear/migrar datos.
- Rutas publicas.

## Aviso de rechazo: recomendacion
**Opcion recomendada: redirigir a "Mis fichas" con un mensaje flash** ("Para gestionar contenido necesitas que un administrador apruebe tu vinculo con una ficha. Podes solicitarlo aca."). Motivos: un 403 es un callejon sin salida, y Mis fichas es exactamente donde se resuelve el problema (buscar y solicitar la ficha). Para peticiones AJAX/JSON (DataTables, store-ajax, selects) se responde 403 JSON con el mismo mensaje, sin redirigir.
Alternativas: (b) pagina 403 propia con boton a Mis fichas (mas clara para quien llega por un enlace, pero requiere un paso extra); (c) modal en la pagina actual (descartada: complejidad y no cubre acceso por URL).
Ademas, un banner permanente en Mis fichas mientras el usuario no tenga fichas.

## Reglas funcionales
1. Colaborador sin fichas: sidebar solo "Mis fichas"; GET a `admin/discos`, `admin/events`, etc. -> redirige a Mis fichas con el aviso.
2. Con ficha de artista: ve Interpretes, Discos, Canciones, Noticias, Eventos; no ve Festivales (si no tiene festival).
3. Con ficha de festival: ve Festivales (y Eventos recien en Etapa 3).
4. Acceder a una URL valida de una seccion no habilitada -> mismo rechazo.
5. Al aprobar su primera ficha el acceso se refleja de inmediato (sin relogin; el calculo es por request).
6. Administrador sin cambios; membresias inactivas/`member`/organizacion inactiva no habilitan secciones.

## Decisiones que necesito del usuario (cambian el acceso de 432 colaboradores)
1. **Primera ficha (circulo vicioso).** Si un colaborador sin fichas solo ve "Mis fichas", no podria *crear* su primer Interprete o Festival (hoy tiene `create interprete` / `create festival`), y solo podria *solicitar* fichas ya existentes. Recomiendo permitir, ademas de Mis fichas, la **creacion** de Interprete y Festival (formulario de alta); al crearla queda como administrador de esa ficha (user_id legacy y organizacion via OrganizationProvisioner), y recien ahi se habilitan las demas secciones.
2. **Mitos, Comidas y demas contenido sin ficha.** Hoy un colaborador crea Mitos/Comidas/Noticias en borrador por permisos globales. Con la regla pedida los perderia hasta tener ficha. Opciones: (a) aplicar la regla tal cual (perderlos); (b) mantener Mitos y Comidas porque no dependen de artistas/festivales (recomendado si queres conservar la colaboracion editorial); (c) mantenerlos solo para quienes ya tengan contenido propio. Impacto: 432 usuarios en produccion si la base local refleja prod (no verificado).
3. **Prensa** (2 usuarios): ¿se trata igual que colaborador?
4. **Usuarios sin rol** (6): mismo tratamiento que colaborador sin fichas.

## Reglas tecnicas
- Sin migraciones. Calculo memoizado por request; el menu no agrega consultas por item (una sola lista de capacidades).
- No cambiar URLs; no cachear respuestas autenticadas.
- Mantener un unico lugar de verdad (config del mapa) para menu y middleware.

## Riesgos
- Cambio de producto con efecto en muchos usuarios existentes: puede cortar flujos vigentes (decisiones 1 y 2). Recomiendo desplegar con un flag de config (`BACKOFFICE_ACCESS_BY_ENTITIES=false` por defecto) para activarlo cuando el equipo este listo.
- Rutas AJAX que hoy dependen de cookies de sesion: validar que el 403 JSON no rompa DataTables.

## Validacion
Tests de las reglas 1-6, regresion completa, y prueba manual con cuentas colaborador sin fichas / con ficha de artista / con ficha de festival.

## Criterio de aceptacion
Un colaborador sin fichas ve solo Mis fichas (mas lo definido en las decisiones) y es redirigido con aviso al intentar otra seccion; con fichas ve y accede solo a lo que habilitan; nada cambia para administradores.

## Aprobacion
Pendiente: decisiones 1-4 y confirmacion de la opcion de aviso.

## Respuestas del usuario (2026-10-11)
1. Primera ficha: correcto el problema; en lugar de alta directa, propone que el usuario **solicite la creacion de la ficha** explicando su relacion (artista, prensa, manager...). Diseno en discusion (ver propuesta en el chat: solicitud de alta dentro del flujo de claims, con busqueda previa y deteccion de duplicados).
2. Mitos/Comidas: **se deja tal cual** hasta terminar los demas puntos; recordarselo al usuario al cerrar (memoria del proyecto).
3. Pregunta sobre prensa/sin rol: pendiente de aclarar.
4. Flag `BACKOFFICE_ACCESS_BY_ENTITIES` (apagado por defecto): aprobado.
