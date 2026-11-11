# Checklist de despliegue v2.6.0 (2026-11-12)

Estado: **preparado en `dev`, no mergeado a `main` ni desplegado**. Nada de esto se ejecutó contra producción; lo hace el responsable del despliegue. Dimensión (`git diff --shortstat main..dev`): 60 archivos, ~3.100 líneas agregadas, 13 commits.

## 0. Qué se despliega
1. **Home** (`home-noticias-sin-fallback`, `home-bloques-catalogo`): 24 noticias, «Ver todas las noticias», cuatro bloques de 4 (próximos eventos, últimos intérpretes, festivales más visitados, últimos discos), sin contador; tarjetas de noticia sin imagen de relleno en todos los listados.
2. **Menús** (`menus-backoffice-y-front`): «Centro de Moderación» primero en el backoffice, «Publicaciones en redes» en «Astronomía y audiencia»; Discos y Canciones en el NAV público detrás de Festivales.
3. **Radios** (`radios-edit-and-moderation`): edición corregida (error 500) y borradores de radios en el Centro de Moderación.
4. **Reactivación de usuarios** (`reactivacion-usuarios`): pantalla de administrador, mail con baja firmada, aviso a los 30 días. Checklist propio: `reactivacion-usuarios-deploy.md`.
5. **Discos** (`discos-estado-en-edicion`): selector de estado en la edición para quien puede publicar y portada contenida en el formulario. Sin migraciones. La spec `discos-spotify-propuestas` es solo documentación (la tarea diaria de Spotify no forma parte del release).
6. **n8n**: export del workflow Facebook+Instagram encadenado y su guía (documentación; no afecta al portal).

Sin cambios de `composer.lock`/dependencias ni variables de entorno obligatorias (opcionales de la reactivación: `REENGAGEMENT_INACTIVITY_DAYS`, `REENGAGEMENT_COOLDOWN_DAYS`, `REENGAGEMENT_MAX_PER_MINUTE`). **No incluye Matecito** (rama `matecito`, pendiente de perfil).

## 1. Migración (una, aditiva)
`2026_11_12_100000_add_reengagement_mail_support` (columna `users.reengagement_opt_out_at` y tabla `reengagement_mail_sends`). Aplicada en local (`mfa`, `mfa_test`) con autorización del usuario.
- [ ] Backup completo de la base de producción, verificado (un archivo vacío no cuenta).
- [ ] `php artisan migrate:status`: debe figurar **solo** esa migración como pendiente.
- [ ] `php artisan migrate`. Reversa: `php artisan migrate:rollback --step=1` (solo se pierden el historial de envíos y las bajas de la reactivación).
- Sin migrar, la pantalla «Reactivación de usuarios» da error (el tablero y el menú están protegidos).

## 2. Antes de subir
- [ ] Tests Feature en verde en `mfa_test` (resultado de la corrida de integración en la sección 6).
- [ ] `public/build` completo y verificado (`npm run build:verify`: 13 assets); el servidor no tiene Node, deben subirse `manifest.json` y todos los `assets/*`.
- [ ] Confirmar que `public/hot` **no** existe en el servidor (si existe, el sitio pide los assets a un servidor Vite inexistente y se ve sin estilos).
- [ ] Anotar el commit de `main` desplegado actualmente (para volver atrás) y copiar el `public/build` actual.

## 3. Despliegue (lo ejecuta el responsable)
1. Merge de `dev` a `main` y push, según el flujo y CI vigentes. Tag `v2.6.0`.
2. En el servidor: `git pull` en `main`; verificar que llegó `public/build` completo.
3. `php artisan migrate` (ver sección 1).
4. `bash scripts/post-deploy.sh` (autoload, cachés y `responsecache:clear`).
5. Confirmar el cron del programador (`schedule:run` cada minuto): procesa la cola, necesaria para la reactivación.

## 4. Verificación posterior
- [ ] `/` responde 200, con CSS de `/build/assets/app-public-*.css`; 24 noticias (sin imagen si no tienen propia), botón «Ver todas las noticias» y los cuatro bloques (de a 4 como máximo; sin datos no se muestran).
- [ ] NAV público: Discos y Canciones junto a Festivales; el desplegable «Más» conserva Clasificados y Contacto. Revisar el ancho en pantallas medianas.
- [ ] Backoffice: «Centro de Moderación» primero y «Publicaciones en redes» en «Astronomía y audiencia».
- [ ] Radios: crear/editar una señal (sin error 500), que una en borrador aparezca en el Centro de Moderación y se publique desde ahí.
- [ ] `/admin/reactivacion-usuarios` abre para un administrador (403 para otros); vista previa de las dos variantes; **«Enviarme una prueba»** y revisar en Gmail; probar «Recuperar mi contraseña».
- [ ] Recién entonces, y solo si se decide, enviar la primera tanda (~440 usuarios) desde esa pantalla.
- [ ] Medir TTFB/LCP de la Home y mirar la tarjeta de eventos en celular (imagen alta en 2 columnas). Sin medición de producción hasta ahora.

## 5. Reversa
Volver al commit anterior de `main` (con su `public/build`), correr `bash scripts/post-deploy.sh` y, si hace falta, `migrate:rollback --step=1`. Las cachés `home:*` caducan solas en 10 minutos.

## 6. Resultado de la corrida de integración
Ver `project/docs/00_estado_actual.md` (sección «Release v2.6.0»).

## 7. Observaciones
- «Inactivo» usa `last_login_at`, que se empezó a registrar hace poco: 442 de 449 usuarios figuran como «nunca ingresó»; la primera tanda es en su mayoría una invitación a activar la cuenta.
- El login con Google/Facebook sigue sin funcionar (próxima tarea); el mail ofrece recuperar contraseña.
- Matecito, los cambios de Discos (estado en edición, propuestas Spotify) y la rama `codex/festival-related-content` **no** forman parte de este release.
