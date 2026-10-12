# Checklist de despliegue a producción — acumulado de `dev` (2026-10-12)

Elaborado contra el estado real del repositorio el 2026-10-12. **Nada de esto se ejecutó en producción**: lo corre la persona responsable del despliegue, paso a paso, marcando cada casilla. Si algún comando difiere del entorno real (por ejemplo, no hay Docker en producción), usar el equivalente del servidor y anotar la diferencia.

## 0. Qué se despliega

| Dato | Valor |
|---|---|
| Rama origen | `dev` (varios commits sin push respecto de `origin/dev`; contar con `git log --oneline origin/dev..dev`) |
| Tamaño | 213 archivos, ≈11.600 líneas agregadas |
| Rama de despliegue | `main` (se hace *pull* de `main` en el servidor; ver `frontend-assets-deploy.md`) |
| Migraciones nuevas | 8 (ver sección 3) |
| Dependencias (composer/npm) | **sin cambios** |
| Build del frontend (`public/build`) | **sin cambios por este lote**; `npm run build:verify` pasa (13 assets). Solo cambió una vista pública (cartelera, valor del JSON-LD) sin clases CSS nuevas |
| Workflows / Docker | solo `ci.yml` (+1 paso: sembrar datos de referencia de tests) |

**Funcionalidad incluida** (detalle en `CHANGELOG.md` → `[Unreleased]`): organizaciones y fichas (vínculo, alta, revisión delegada al responsable), contenido de artistas y festivales para sus administradores (discos, canciones, eventos, noticias), eventos con jornadas y salas, rol `verificado` y baja del rol `prensa`, correcciones de permisos, acceso por fichas (detrás de un flag, apagado), login sin botones sociales, description del JSON-LD de la cartelera. **Se elimina el flujo público "Publicar / Contribuir"** (`/admin/contribuir` y su moderación): "Publicar" lleva a `/admin`.

## 1. Antes de empezar (decisiones y pruebas pendientes de **vos**)

- [ ] Validar a mano en local (navegador) los flujos de la sección 8. Nunca se probaron visualmente; solo hay tests automáticos.
- [ ] Decidir si el despliegue activa o no el flag `BACKOFFICE_ACCESS_BY_ENTITIES`. **Recomendado: desplegar con el flag APAGADO** y activarlo en una segunda etapa (sección 10).
- [ ] Avisar al equipo/colaboradores si corresponde (sección 11).
- [ ] Elegir ventana de bajo tráfico. Duración estimada del paso de base de datos: minutos.

## 2. Respaldos y verificaciones previas (producción)

- [ ] **Backup completo de la base de datos** y verificar que se puede abrir/restaurar (un archivo vacío o no verificado no cuenta como respaldo).
- [ ] Copia del código actual de producción y del `public/build` actual.
- [ ] Anotar el commit de `main` actualmente desplegado (para volver atrás).
- [ ] Consultas de **solo lectura** para conocer el punto de partida:
  ```sql
  -- usuarios por rol (el rol `prensa` desaparece en la migración de roles)
  SELECT r.name, COUNT(mhr.model_id) AS usuarios FROM roles r LEFT JOIN model_has_roles mhr ON mhr.role_id = r.id GROUP BY r.name;
  -- ¿existe ya el permiso de publicar?
  SELECT name FROM permissions WHERE name = 'publish contents';
  -- solicitudes de vínculo existentes (quedan como kind=link)
  SELECT COUNT(*) FROM entity_claim_requests;
  -- migraciones aplicadas
  SELECT COUNT(*) FROM migrations;
  ```
- [ ] **Flujo "Contribuir" (se retira en este despliegue).** El responsable confirmó que **no hay contribuciones en producción**. Verificarlo igual antes de migrar, porque la migración **borra** la tabla `contributions` y las columnas `users.points` / `users.rank`:
  ```sql
  SELECT COUNT(*) FROM contributions;                 -- esperado: 0
  SELECT COUNT(*) FROM users WHERE points > 0;        -- esperado: 0 (los puntos solo se otorgaban al aprobar contribuciones)
  ```
  - [ ] Si alguna consulta devuelve más de 0: **parar** y avisar; el respaldo completo del paso 2 permite recuperar los datos, y el `down()` de la migración recrea la estructura (no los datos).
- [ ] **Formulario de contacto:** la tabla `contact_messages` no existía en ninguna migración y puede existir en producción como tabla heredada. Antes de migrar, **solo lectura**:
  ```sql
  DESCRIBE contact_messages;                 -- ¿existe? ¿columnas id, nombre, email, asunto, mensaje, fecha_envio?
  SELECT COUNT(*) FROM contact_messages;     -- anotar cuántos mensajes hay
  ```
  - [ ] Si existe con otro esquema (otros nombres de columnas): **parar y avisar** antes de migrar; la migración solo agrega columnas, pero el código espera esas 6 columnas heredadas.
  - [ ] Confirmar que `MAIL_*` de producción envía correo (el formulario avisa a `CONTACT_NOTIFY_TO`).
- [ ] Anotar cuántos usuarios tienen rol `prensa`. En local eran 2 de prueba; **si en producción hay usuarios reales con ese rol, la migración los pasa a `colaborador`** (pierden `update album/cancion/noticia/show`, que de todos modos ya no tienen efecto cruzado). Avisarles si corresponde.
- [ ] Revisar `storage/logs/laravel.log` reciente: línea base de errores antes de desplegar.

## 3. Migraciones (las ejecuta una persona, mostrando antes el estado)

Nuevas, en este orden:

| Migración | Qué hace | Riesgo |
|---|---|---|
| `2026_10_08_120000_create_event_programs` | Tabla de programas/jornadas de eventos | Bajo (tabla nueva) |
| `2026_10_08_130000_create_organization_entities_and_contact_metadata` | Vincula organizaciones con fichas + metadatos de contacto | Bajo (tabla/columnas nuevas) |
| `2026_10_10_120000_add_contact_tracking_to_organizations` | Seguimiento de contacto en organizaciones | Bajo |
| `2026_10_10_140000_create_entity_claim_requests_table` | Solicitudes de vínculo usuario↔ficha | Bajo |
| `2026_10_11_120000_add_creation_requests_to_entity_claim_requests` | Solicitudes de alta; `entity_id` pasa a nullable (ALTER) y 4 columnas nuevas | Medio-bajo (ALTER sobre tabla chica) |
| `2026_10_12_100000_trusted_publishing_role_and_remove_prensa_role` | Crea permiso `publish contents` y rol `verificado`; **pasa usuarios `prensa` a `colaborador` y elimina el rol `prensa`** | **Medio: modifica datos de roles** |
| `2026_10_13_100000_create_or_extend_contact_messages_table` | Crea `contact_messages` si no existe; si existe (tabla heredada) **solo agrega** las columnas que falten (`status`, `user_id`, `ip_hash`, `read_at`, `answered_at`, `spam_reason`); no modifica ni borra nada | Bajo (aditiva). Los mensajes previos quedan con estado `new` |
| `2026_10_12_140000_drop_contributions_and_user_points_rank` | **Borra** la tabla `contributions` y las columnas `users.points` / `users.rank` (flujo "Contribuir" retirado) | **Medio: destructiva** (sin datos en producción, confirmado; `down()` recrea la estructura, no los datos) |

- [ ] `php artisan migrate:status` → deben figurar **exactamente esas 8** como *Pending*. Si hay más o menos, parar y revisar.
- [ ] `php artisan migrate --pretend` → leer el SQL que se ejecutaría (no ejecuta nada).
- [ ] `php artisan migrate --force` (con el usuario de la aplicación para no crear archivos como root).
- [ ] `php artisan migrate:status` → 0 pendientes.
- [ ] Verificar el resultado:
  ```sql
  SELECT r.name, COUNT(mhr.model_id) FROM roles r LEFT JOIN model_has_roles mhr ON mhr.role_id = r.id GROUP BY r.name;   -- sin `prensa`; aparece `verificado` con 0
  SELECT name FROM permissions WHERE name = 'publish contents';                                                  -- 1 fila
  SELECT COUNT(*) FROM entity_claim_requests WHERE kind = 'link';
  SHOW TABLES LIKE 'contributions';                                                                        -- 0 filas (la tabla ya no existe)
  SELECT COUNT(*) FROM users;                                                                             -- = total previo (no se pierden usuarios)                                               -- = total previo
  ```
- [ ] **No** ejecutar `migrate:fresh`, `migrate:refresh` ni seeders de demo en producción. `AccessValidationAccountsSeeder` y `TestingReferenceDataSeeder` están protegidos (no corren en producción) y no deben usarse.
- [ ] `down()`: el de roles recrea el rol `prensa` con sus permisos pero **no reasigna usuarios**. Si hace falta volver atrás, restaurar el backup.

## 4. Código y entorno

- [ ] Integrar `dev` en `main` por el flujo de revisión habitual y esperar el **CI en verde** (debe correr la suite completa; en local, base limpia: 560 pasan, 0 fallan, 2 omitidos).
- [ ] Primer push de `dev`: **mirar el resultado del CI antes de integrar a `main`**. Es la primera vez que corre el paso nuevo `Seed reference data for tests`; si falla, corregirlo antes de seguir.
- [ ] Servidor: `git pull` de `main`.
- [ ] `.env` de producción:
  | Variable | Valor recomendado | Nota |
  |---|---|---|
  | `BACKOFFICE_ACCESS_BY_ENTITIES` | `false` (u omitir) | Activar recién en la etapa 2 |
  | `ENTITY_MANAGEMENT_SCOPE_LISTINGS` | `false` (u omitir) | |
  | `ENTITY_CLAIMS_DELEGATE_TO_OWNERS` | `true` (u omitir) | Los owners aprueban editores de su ficha |
  | `SOCIAL_LOGIN_ENABLED` | **no definir** (u omitir/`false`) | Oculta los botones de Google/Facebook |
  | `CONTACT_NOTIFY_TO` | casilla que recibe los avisos (por defecto la actual) | Aviso de cada mensaje de contacto nuevo |
  | `CONTACT_SEND_CONFIRMATION` | `true` (o `false` para no escribir a terceros) | Confirmación al remitente |
  - [ ] No copiar el `.env` local ni el `.env.testing`: tienen valores de desarrollo (`.env.testing` apunta a `mfa_test`).
- [ ] `composer install --no-dev` **no** es necesario (sin cambios de dependencias); correrlo solo si el proceso habitual lo exige.
- [ ] Frontend: **`public/build` cambió** (3 CSS con hash nuevo + `manifest.json`, por las clases del formulario de contacto): viaja en el commit; confirmar tras el *pull* que `public/build/manifest.json` apunta a archivos existentes (`npm run build:verify` local pasa) y que `public/hot` no existe.

## 5. Cachés (después de migrar y de actualizar el código)

- [ ] `php artisan optimize:clear` (incluye la caché de vistas: el menú de usuario y la traducción "Mis datos" en `resources/lang/vendor/adminlte/es/menu.php` se leen al renderizar)
- [ ] `php artisan permission:cache-reset` (caché de roles/permisos de Spatie; imprescindible tras la migración de roles)
- [ ] `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` (la vista de login cambió)
- [ ] Limpiar la caché de respuestas públicas (response cache) si está activa, para que la cartelera regenere la description del JSON-LD.
- [ ] Si se usa OPcache sin revalidación, recargar PHP-FPM.
- [ ] Colas/Horizon: sin cambios en jobs, no requiere reinicio específico (reiniciar workers por buena práctica si el proceso lo prevé).

## 6. Smoke test inmediato (sin sesión)

- [ ] Home y una página interna → `200`; CSS/JS → `200`.
- [ ] `/login` y `/register`: **sin** botones de Google/Facebook; formulario de email y contraseña visible.
- [ ] Botón **Publicar** del menú → lleva al login; tras iniciar sesión cae en `/admin` (con el flag apagado, el dashboard; con el flag encendido y usuario no administrador, Mis fichas).
- [ ] **Contacto:** `/contacto` carga; el menú **Más → Contacto** y el footer (**Escribinos** y la lista) lo enlazan; enviar un mensaje de prueba → confirmación en pantalla, correo al equipo (con Reply-To del remitente) y, si está activo, confirmación al remitente; aparece en **Backoffice → Mensajes de contacto** (solo administrador) y en el dashboard. Probar también que un segundo envío idéntico no duplica y que un envío inmediato (menos de 4 s) muestra el aviso. Borrar el mensaje de prueba.
- [ ] `/admin/contribuir` y `/admin/contributions` responden `404`; las páginas de artista, festival, canción y evento ya no muestran "Sugerir corrección".
- [ ] **Mis datos, etapa 3** (sin migración): verificar `SESSION_DRIVER` en producción (la tarjeta **Sesiones activas** solo aparece con `database`); crear dos sesiones (dos navegadores), **Cerrar las otras sesiones** con la contraseña y confirmar que la otra se desloguea; cambiar la contraseña y ver que cierra las demás; aprobar/rechazar una solicitud de prueba y confirmar que llega el correo al solicitante (con el rol o el motivo); con una **cuenta de prueba** (nunca una real) probar **Eliminar mi cuenta** (contraseña + `ELIMINAR`): queda como "Usuario eliminado N", no puede volver a ingresar y su contenido sigue publicado sin su nombre. Una cuenta administradora no puede eliminarse desde ahí.
- [ ] **Menú de usuario** (esquina superior derecha del backoffice): avatar de iniciales, nombre, rol y botones **Mis datos** / **Salir**. En **Mis datos** (`/admin/mi-cuenta`): cambiar nombre y teléfono, cambiar contraseña (pide la actual; mínimo 8), ver el resumen y suscribirse/darse de baja del newsletter. El email aparece solo lectura. Verificar también que `/admin/users` (crear/editar) muestra el teléfono y exige contraseña de 8 caracteres o más.
- [ ] Una página de **artista** (home y biografía) y una de **festival** muestran, discreto, "¿Sos este artista o lo representás? / ¿Organizás este festival? Solicitá administrar su ficha"; el enlace pasa por el login y cae en **Mis fichas** con la ficha ya elegida. Las páginas de evento y canción no lo muestran.
- [ ] `/cartelera-de-eventos-folkloricos` → `200`; una ficha de evento → `200`. En el HTML de la cartelera, cada evento del JSON-LD tiene `description` no vacía.
- [ ] `storage/logs/laravel.log` sin errores nuevos.

## 7. Smoke test con sesión

- [ ] **Administrador:** entra al backoffice; ve Usuarios y Roles → Organizaciones y Solicitudes de vínculo; la lista de roles no incluye `prensa` y sí `verificado`.
- [ ] **Colaborador existente:** inicia sesión sin errores; ve "Mis fichas".
- [ ] Un usuario sin rol puede registrarse por email y contraseña.

## 8. Validación manual por flujo (con cuentas de prueba reales de producción)

Crear 3 cuentas de prueba desde el backoffice (colaborador) y borrarlas o desactivarlas al terminar. No usar `AccessValidationAccountsSeeder` (solo local).

1. **Mis fichas, vínculo:** el colaborador busca un artista de prueba y solicita el vínculo → el administrador ve la solicitud con **rol sugerido y alertas** → aprueba → el colaborador ve la ficha en "Fichas que administro".
2. **Mis fichas, alta:** pedir la creación de una ficha inexistente → el administrador crea la ficha mínima (sin publicar) o la vincula a un duplicado.
3. **Revisión delegada:** con un owner activo, otra cuenta pide ser editor → el owner lo aprueba/rechaza desde "Solicitudes para mis fichas".
4. **Contenido de artista:** un editor crea un disco, una canción, un evento y una noticia de su artista → quedan **pendientes de revisión** y el administrador los ve/publica. Un editor **no** puede editar contenido de otro artista.
5. **Eventos de festival:** el administrador de un festival crea un evento del festival; no puede editar eventos de un festival ajeno.
6. **Eventos con jornadas y salas:** crear evento de una fecha y de varias jornadas; revisar listado, edición, salas/lugares y cartelera pública.
7. **Camino de confianza:** asignar `verificado` (además de `colaborador`) a una cuenta de prueba → lo que crea para su artista se publica directo; sin `verificado`, queda en revisión.
8. **Permisos:** un usuario que no sea administrador **no** puede listar usuarios; un colaborador no puede borrar.

## 9. Observabilidad y SEO

- [ ] Revisar `laravel.log` durante las primeras horas y al día siguiente.
- [ ] Search Console: el aviso de *description faltante* en eventos. La corrección es del valor del JSON-LD de la cartelera; **no se puede afirmar que resuelva el aviso** hasta pedir la revalidación y ver el informe (puede demorar días).
- [ ] Recordar: hay eventos históricos sin texto de detalle (`body`) que siguen necesitando curación editorial.

## 10. Etapa 2 (separada): activar el acceso por fichas

Solo después de unos días estables y de la validación manual:

- [ ] Consultas de solo lectura para dimensionar el impacto:
  ```sql
  -- autores de mitos, comidas y noticias por rol (en local: casi todo es de administradores)
  SELECT 'mitos', COALESCE(r.name,'sin rol'), COUNT(*) FROM mitos m LEFT JOIN model_has_roles mhr ON mhr.model_id = m.user_id LEFT JOIN roles r ON r.id = mhr.role_id GROUP BY r.name;
  SELECT 'comidas', COALESCE(r.name,'sin rol'), COUNT(*) FROM comidas c LEFT JOIN model_has_roles mhr ON mhr.model_id = c.user_id LEFT JOIN roles r ON r.id = mhr.role_id GROUP BY r.name;
  SELECT 'news', COALESCE(r.name,'sin rol'), COUNT(*) FROM news n LEFT JOIN model_has_roles mhr ON mhr.model_id = n.created_by LEFT JOIN roles r ON r.id = mhr.role_id GROUP BY r.name;
  ```
- [ ] **Selección acotada (solo con el flag activo):** con una cuenta colaborador que administra un artista, comprobar que en Discos y Canciones solo se ofrece ese artista, que en Noticias el principal solo permite artistas propios (los secundarios, cualquiera), que un Evento exige al menos un artista o festival propio y que Intérpretes lista solo las fichas propias. El administrador sigue viendo y eligiendo todo.
- [ ] Qué cambia al activar `BACKOFFICE_ACCESS_BY_ENTITIES=true`: quien no administra fichas deja de ver Intérpretes, Discos, Canciones, Festivales, Eventos y Noticias; Mitos y Comidas quedan para administradores y `verificado`. Mis fichas siempre está disponible. Lo ya publicado no cambia.
- [ ] Activar, `php artisan config:cache`, repetir el smoke con una cuenta colaborador sin fichas, con ficha de artista, con ficha de festival y con `verificado`.
- [ ] Reversa inmediata: `BACKOFFICE_ACCESS_BY_ENTITIES=false` + `php artisan config:cache`.

## 11. Comunicación sugerida

- [ ] Equipo editorial: cómo revisar solicitudes (rol sugerido y alertas), el flujo de alta de fichas y quién asigna `verificado`.
- [ ] Colaboradores (al activar el flag): qué secciones cambian y cómo pedir el vínculo con su artista o festival.
- [ ] Owners de fichas: ahora aprueban a los editores de su ficha.

## 12. Plan de reversa

| Qué falla | Qué hacer |
|---|---|
| Error general tras el *pull* | Volver `main` al commit anotado, `optimize:clear` + `permission:cache-reset`, cachear de nuevo |
| Restricciones inesperadas de acceso | `BACKOFFICE_ACCESS_BY_ENTITIES=false` + `config:cache` (no requiere tocar datos) |
| Problema con la delegación | `ENTITY_CLAIMS_DELEGATE_TO_OWNERS=false` + `config:cache` |
| Problema con roles/permisos | Restaurar el backup de la base (el `down()` de la migración de roles no reasigna usuarios) |
| Login | Reponer botones sociales con `SOCIAL_LOGIN_ENABLED=true` (solo si el login social funciona) |

## 13. Pendientes conocidos que **no** bloquean el despliegue

- 2 tests omitidos de larga data (`NoticiasBugFixTest` entre ellos).
- `RolesAndPermissionsSeeder` histórico está desfasado (aún crea el rol `prensa`): no ejecutarlo en entornos con datos; evaluar retirarlo.
- Mitos y Comidas son solo de administradores y `verificado`. El flujo "Contribuir" ya no existe: si aparece demanda de aportes de colaboradores, habría que diseñar un camino nuevo dentro del circuito de fichas y revisión.
- Cierre de versión: pasar `[Unreleased]` del CHANGELOG a una versión (`v2.4.0` sugerida, a decidir) y archivar las specs de OpenSpec una vez validado en producción. `openspec/` está en `.gitignore`: las specs se versionaron con `git add -f`.
