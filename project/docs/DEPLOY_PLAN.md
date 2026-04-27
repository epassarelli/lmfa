# Plan de Deploy — v2.0.0

> Este documento se actualiza con cada cambio que impacte en producción.
> Ejecutar **en orden** al hacer el deploy. Nada de esta lista es opcional.

---

## 1. Pre-deploy (antes de subir código)

- [ ] Hacer backup completo de la base de datos de producción
- [ ] Verificar que `APP_KEY` esté seteada en producción (requerida para los campos `encrypted` de `SocialAccount`)
- [ ] Verificar que `QUEUE_CONNECTION=database` en `.env` de producción

---

## 2. Subir código

```bash
git fetch origin
git checkout main
git pull --ff-only origin main
git rev-parse --short HEAD  # debe ser 65e15af o posterior
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

---

## 3. Migraciones pendientes

Ejecutar en orden. Revisar con `php artisan migrate:status` antes de correr.

No hay migraciones pendientes para v2.0.0. La tabla `user_notifications` ya existía con el nombre correcto.

---

## 4. Seeders

Solo correr si los roles/permisos no existen en producción (DB vacía o setup inicial).
**No correr en una DB con datos reales de roles** — duplica registros.
El seeder `RolesAndPermissionsSeeder` no es idempotente (`create()` directo).

Antes de decidir, verificar roles y permisos críticos:

```bash
# Verificar primero:
php artisan tinker --execute="echo \Spatie\Permission\Models\Role::count();"
php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::where('name', 'publish contents')->exists() ? 'ok' : 'missing';"

# Solo si devuelve 0:
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

---

## 5. Limpiar y reconstruir caches

```bash
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan permission:cache-reset   # Spatie — limpiar caché de roles/permisos
php artisan queue:restart             # Reiniciar workers para que tomen el código nuevo
```

---

## 6. Verificar post-deploy

- [ ] `/admin/moderation` carga sin errores
- [ ] `/admin/classifieds` — solapas funcionan
- [ ] `/admin/contributions` — moderación de contribuciones visible solo para admin
- [ ] `/admin/contribuir` — panel personal del colaborador carga para usuario autenticado
- [ ] `/admin/news` carga sin errores
- [ ] `/admin/events` carga sin errores
- [ ] API `POST /api/v1/news` con token de usuario no-admin devuelve 403
- [ ] Detalle de noticia con URL `/{interprete}/noticias/{slug}` carga correctamente
- [ ] Detalle de intérprete (`/abel-pintos/biografia`) carga sin errores de ruta

---

## 7. Cambios en componentes y librerías

| Cambio | Tipo | Estado |
|--------|------|--------|
| Spatie `role`/`permission`/`role_or_permission` registrados en Kernel | Config código | ✅ En rama |
| `UserNotification` apunta a tabla `user_notifications` | Modelo | ✅ En rama |
| API rutas escritura protegidas con `role:administrador` | Rutas | ✅ En rama |
| `SocialAccount`: cast `encrypted` en `token_encrypted` y `refresh_token_encrypted` | Modelo | ✅ Ya estaba |
| Policies de Album, Cancion, Festival, Mito, Comida registradas en `AuthServiceProvider` | Config código | ✅ En rama |
| `authorizeResource` agregado en News, Album, Cancion, Festival, Mito, Comida controllers | Código | ✅ En rama |
| `Classified.is_active` convertido en accessor (no escribe en BD) | Modelo | ✅ En rama |
| Perfiles de imagen: festival, mito, event agregados | Config | ✅ En rama |
| `PublicationRequest`: constantes STATUS_* definidas | Modelo | ✅ En rama |
| API: recurso `events` expuesto en `/api/v1/events` | Rutas | ✅ En rama |
| **GAP-21 API versioning**: diferido. Estrategia a definir antes de v3.0.0. Opciones: (a) route groups paralelos `v1/v2`, (b) Laravel API Resources como capa de transformación. No bloquea v2.0.0. | Arquitectura | ⏳ Pendiente |

---

## 8. Variables de entorno nuevas o modificadas

| Variable | Requerida | Descripción |
|----------|-----------|-------------|
| `APP_KEY` | ✅ Sí | Requerida para el cast `encrypted` de `SocialAccount` |
| `PORTAL_ORGANIZATION_ID` | ⚠️ Opcional | ID de la organización institucional del portal. Si no se configura, la opción "publicar en redes del portal" no creará targets (loguea warning). Setear antes de usar la Pasarela en producción. |

### Advertencia sobre tokens existentes

Si producción ya tiene filas en `social_accounts` creadas antes del cast `encrypted`, los tokens pueden estar en texto plano y fallar al leerse con el cast nuevo.

Verificar antes de usar la Pasarela:

```sql
SELECT COUNT(*) FROM social_accounts;
```

Si devuelve más de 0, reingresar/rotar tokens desde el panel o preparar conversión controlada.

---

## Historial de versiones deployadas

| Versión | Fecha | Notas |
|---------|-------|-------|
| v2.0.0-beta | — | Primera versión rama v2, pendiente de tagear |
