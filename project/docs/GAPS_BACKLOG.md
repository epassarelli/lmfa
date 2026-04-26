# Backlog de Gaps — v2.0.0

> Gaps encontrados durante la auditoría de estabilidad (2026-04-26).
> Ordenados por prioridad. Atacar de arriba hacia abajo.
>
> Severidad: 🔴 Bug crítico · 🟠 Seguridad · 🟡 Lógica · 🔵 UX/Faltante · ⚪ Deuda técnica
> Esfuerzo: S = < 1h · M = 1-4h · L = > 4h

---

## 🔴 Bugs Críticos

### GAP-01 · ModerationController: query de noticias sin agrupar
**Módulo:** Moderación editorial  
**Esfuerzo:** S

La query actual usa `->orWhereNull('published_at')` sin agrupar, lo que hace que el `OR` aplique a toda la tabla y no solo a los drafts. El panel puede devolver miles de noticias activas que simplemente no tienen `published_at` registrado.

```php
// Actual (incorrecto):
News::where('editorial_status', 'draft')->orWhereNull('published_at')

// Correcto:
News::where(function($q) {
    $q->where('editorial_status', 'draft')->orWhereNull('published_at');
})
```

**Archivo:** `app/Http/Controllers/Backend/ModerationController.php:22`

---

### GAP-02 · UGC: slug collision al aprobar contenido nuevo
**Módulo:** Contribuciones UGC  
**Esfuerzo:** S

Cuando se aprueba una contribución de tipo "nuevo contenido", se genera el slug desde el título/artista/canción. Si ya existe un registro con ese slug, el `$model->save()` falla con un error de unique constraint sin manejo. La transacción queda en estado inconsistente.

**Archivo:** `app/Http/Controllers/Backend/ContributionController.php:55-65`  
**Fix:** Usar `Str::slug() + uniqueSuffix` o `Str::slug() + incremento` con `whereSlug` previo.

---

### GAP-03 · Pasarela: EventReminderJob asigna requests al usuario id=1
**Módulo:** Pasarela / Scheduler  
**Esfuerzo:** S

`PublicationService::createRequest` usa `Auth::id() ?? 1`. El scheduler no tiene sesión autenticada, por lo que todos los `PublicationRequest` creados automáticamente quedan asignados al usuario con `id=1`.

**Archivo:** `app/Services/Publication/PublicationService.php:29`  
**Fix:** Recibir `$userId` como parámetro en `createRequest()` y que el job pase el `created_by` del evento.

---

## 🟠 Seguridad

### GAP-04 · API: cualquier token Sanctum puede hacer DELETE/PUT en cualquier entidad
**Módulo:** API REST  
**Esfuerzo:** M

Los endpoints `store`, `update` y `destroy` de todos los recursos API no verifican rol. Un usuario registrado común puede eliminar cualquier artista, noticia o álbum con un token válido.

**Archivos:** Todos los controllers en `app/Http/Controllers/Api/`  
**Fix:** Agregar `$this->middleware('role:administrador')->except(['index', 'show'])` en cada controller API, o un middleware global para el grupo `api/v1`.

---

### GAP-05 · SocialAccount: token almacenado en texto plano
**Módulo:** Pasarela / Cuentas sociales  
**Esfuerzo:** S

El campo `token_encrypted` almacena el token de acceso a redes sociales sin ningún cifrado. Cualquier acceso a la base de datos expone todos los tokens.

**Archivo:** `app/Http/Controllers/Pasarela/SocialAccountController.php:83`  
**Fix:** Agregar cast `encrypted` al campo `token_encrypted` en el modelo `SocialAccount` (Laravel lo cifra/descifra automáticamente con `APP_KEY`).

---

### GAP-06 · Permisos granulares definidos pero no chequeados
**Módulo:** Backend general  
**Esfuerzo:** L

Los roles `prensa` y `colaborador` existen con permisos Spatie bien definidos, pero todos los controllers del backend solo verifican `role:administrador`. Un usuario `prensa` o `colaborador` ve un 403 en todo el backend, o entra como admin si el middleware no está puesto. El sistema de permisos granulares no tiene efecto real.

**Fix:** Definir qué controllers deben abrirse a `prensa`/`colaborador` y reemplazar `role:administrador` por `permission:access {entidad}` o `role:administrador|prensa` según corresponda.

---

## 🟡 Lógica

### GAP-07 · Pasarela: `wants_portal_social` publica en todas las organizaciones
**Módulo:** Pasarela / PublicationService  
**Esfuerzo:** M

Cuando el publicador elige "redes del portal", la query busca `SocialAccount::where('owner_type', Organization::class)` sin filtrar por organización. Si hay múltiples organizaciones en el sistema, publica en los canales de todas.

**Archivo:** `app/Services/Publication/PublicationService.php:86-103`  
**Fix:** Definir una organización "institucional del portal" (por config o flag en la tabla) y filtrar por ella.

---

### GAP-08 · Pasarela: admin no puede ver solicitudes de otros usuarios
**Módulo:** Pasarela / PublicationRequestController  
**Esfuerzo:** S

`authorizeRequest()` verifica que `requested_by === Auth::id()`. Un administrador que quiere revisar la solicitud de otro usuario recibe un 403.

**Archivo:** `app/Http/Controllers/Pasarela/PublicationRequestController.php:169-172`  
**Fix:** Excepcionar a usuarios con rol `administrador`:
```php
if ($publicationRequest->requested_by !== Auth::id() && !Auth::user()->hasRole('administrador')) {
    abort(403);
}
```

---

### GAP-09 · UGC: rango inicial vacío en el panel del colaborador
**Módulo:** Contribuciones UGC  
**Esfuerzo:** S

Usuarios nuevos tienen `rank = null`. El panel del colaborador muestra "Rango: ({{ Auth::user()->points }} pts)" en blanco, lo que se ve roto.

**Fix:** Agregar valor por defecto en la migración de users o un accessor en `User::getRankAttribute()` que devuelva `'Colaborador'` si `rank` es null.

---

### GAP-10 · Clasificados: `expiration_date` calculada dos veces sin efecto
**Módulo:** Clasificados  
**Esfuerzo:** S

Al crear un aviso se fija `expiration_date = now()->addDays(30)`, pero al aprobar el admin la pisa con otro `now()->addDays(30)`. El primer valor nunca tiene efecto y puede confundir: si un aviso tarda 15 días en moderarse, expirará 30 días después de la aprobación (correcto), no de la creación.

**Archivos:** `app/Http/Controllers/Frontend/ClassifiedsController.php:113` y `app/Http/Controllers/Backend/ClassifiedController.php:97`  
**Fix:** Quitar `expiration_date` del `store` del frontend. Solo el admin la asigna al aprobar.

---

### GAP-11 · UserNotification usa la tabla `notifications` (colisión con Laravel)
**Módulo:** Pasarela / Notificaciones  
**Esfuerzo:** M

`UserNotification` usa `$table = 'notifications'`, el mismo nombre que usa el sistema nativo de Laravel (`Notifiable` trait en `User`). Los schemas son incompatibles. Si algún código usa `$user->notify()` o `$user->notifications`, colisiona.

**Archivo:** `app/Models/UserNotification.php:13`  
**Fix:** Renombrar la tabla a `user_notifications` con una migración, o asegurarse de que el trait `Notifiable` en `User` nunca se use activamente.

---

## 🔵 UX / Funcionalidad Faltante

### GAP-12 · Sin notificación al usuario al moderar contribuciones UGC
**Módulo:** Contribuciones UGC  
**Esfuerzo:** S

Cuando un administrador aprueba o rechaza una contribución, el usuario que la envió no recibe ninguna notificación (ni email ni in-app).

**Archivo:** `app/Http/Controllers/Backend/ContributionController.php`  
**Fix:** Llamar a `UserNotification::notify($contribution->user_id, ...)` en `approve()` y `reject()`.

---

### GAP-13 · Sin notificación al usuario al moderar clasificados
**Módulo:** Clasificados  
**Esfuerzo:** S

Mismo problema que GAP-12. El anunciante no sabe si su aviso fue aprobado o rechazado.

**Archivo:** `app/Http/Controllers/Backend/ClassifiedController.php`  
**Fix:** Llamar a `UserNotification::notify($classified->user_id, ...)` en `approve()` y `reject()`.

---

### GAP-14 · Pasarela: sin reintento manual de targets fallidos
**Módulo:** Pasarela  
**Esfuerzo:** M

No existe ruta ni botón en la UI para reintentar un `PublicationTarget` en estado `failed` una vez que se agotaron los 3 reintentos automáticos del job.

**Fix:** Agregar ruta `POST /admin/pasarela/publication-requests/{request}/targets/{target}/retry` que re-encole `PublishToProviderJob` y resetee el status a `pending`.

---

### GAP-15 · Moderación editorial sin acciones directas
**Módulo:** Moderación editorial  
**Esfuerzo:** M

El panel `/admin/moderation` solo muestra un link "Revisar" que lleva al formulario de edición de cada entidad. No hay aprobación/rechazo inline. Para publicar una noticia hay que ir al formulario de edición y cambiar manualmente `editorial_status`.

**Fix:** Agregar botones de aprobación directa en el panel de moderación con rutas `POST /admin/{entidad}/{id}/publish`.

---

## ⚪ Deuda Técnica

### GAP-16 · Clasificados: campo `is_active` redundante con `estado`
**Módulo:** Clasificados  
**Esfuerzo:** S

`is_active` (bool) y `estado` (enum) siempre se actualizan juntos y representan lo mismo. Duplica lógica y puede generar inconsistencias si en algún momento se olvida actualizar uno de los dos.

**Fix:** Eliminar `is_active` y reemplazar sus usos por `$classified->estado === 'activo'`, o convertirlo en un accessor calculado.

---

### GAP-17 · UGC: type `show` resuelve a alias deprecated
**Módulo:** Contribuciones UGC  
**Esfuerzo:** S

El type `show` en el formulario de contribuciones resuelve a `App\Models\Show`, que es un alias de `Event` marcado como `@deprecated`. Si en el futuro se elimina la clase `Show`, el UGC de tipo `show` rompe silenciosamente.

**Archivo:** `app/Http/Controllers/Frontend/ContributionController.php:13`  
**Fix:** Cambiar `'show'` por `'event'` en `ALLOWED_TYPES` y actualizar el formulario `create.blade.php`.

---

### GAP-18 · Perfiles de imagen faltantes para Festival, Mito y Show/Event
**Módulo:** Sistema de imágenes  
**Esfuerzo:** S

`config/image_profiles.php` no tiene perfiles para `festival`, `mito` ni `event/show`. Si se quiere agregar upload de imágenes a esos módulos via `ImageUploadService`, hay que crear el perfil primero.

**Archivo:** `config/image_profiles.php`  
**Fix:** Agregar perfiles `festival`, `mito` y `event` con las variantes apropiadas.

---

### GAP-19 · Sin límite de imágenes por modelo
**Módulo:** Sistema de imágenes  
**Esfuerzo:** S

`ImageUploadService::process` con `$replace=false` acumula `MediaAsset` indefinidamente. En el store de clasificados se itera sobre los archivos sin controlar cuántas imágenes ya tiene el modelo.

**Archivo:** `app/Services/ImageUploadService.php` y `app/Http/Controllers/Frontend/ClassifiedsController.php:121-125`  
**Fix:** Verificar count de imágenes antes de procesar y limitar (ej: máx. 5 por modelo).

---

### GAP-20 · API: sin recurso `events`
**Módulo:** API REST  
**Esfuerzo:** M

Los Eventos/Shows no están expuestos en la API. Si se integra una app móvil o un sistema externo, no puede consultar ni crear eventos.

**Fix:** Crear `app/Http/Controllers/Api/EventController.php` y agregar `Route::apiResource('events', EventController::class)` en `routes/api.php`.

---

### GAP-21 · API: sin versioning real
**Módulo:** API REST  
**Esfuerzo:** L

El prefix `v1` existe en las rutas pero no hay mecanismo de deprecación, headers de versión, ni forma de introducir `v2` sin romper `v1`. Cualquier cambio de modelo rompe consumidores existentes sin aviso.

**Fix:** Estrategia a definir: route groups paralelos, o response transformers (Laravel Resources) que desacoplen el modelo del contrato de API.

---

### GAP-22 · News: doble campo de estado (`estado` legacy + `editorial_status`)
**Módulo:** Noticias  
**Esfuerzo:** M

`News` tiene dos campos de estado simultáneos: `estado` (int 0/1, legacy) y `editorial_status` (string `draft`/`published`, nuevo). Parte del código filtra por `editorial_status`, otra parte por `estado`. Un registro puede quedar con valores inconsistentes (ej: `estado=1` y `editorial_status='draft'`).

**Archivos:** `app/Models/News.php`, queries en controllers y vistas  
**Fix:** Migrar todos los usos a `editorial_status`, eliminar el campo `estado` con una migración y un script de normalización de datos existentes.

---

### GAP-23 · Event: triple campo de estado (`estado` + `editorial_status` + `status`)
**Módulo:** Eventos  
**Esfuerzo:** M

`Event` acumula tres campos de estado: `estado` (int legacy), `editorial_status` (`draft`/`published`) y `status` (string, único valor conocido: `active`). El rol de cada uno se superpone y no está definido cuál es canónico. El accessor `estado` mapea desde `editorial_status`, pero `status` no tiene relación clara con ninguno de los otros dos.

**Archivos:** `app/Models/Event.php`, migraciones  
**Fix:** Definir cuál campo es canónico (probablemente `editorial_status`), eliminar `estado` como campo real (dejar solo el accessor si hace falta compatibilidad) y clarificar el rol de `status` (¿ciclo de vida del evento vs. estado editorial?).

---

### GAP-24 · PublicationRequest: valores de `status` no documentados ni validados
**Módulo:** Pasarela  
**Esfuerzo:** S

El campo `status` de `PublicationRequest` es un string libre con default `pending`. No hay enum, constante ni validación que enumere los valores posibles. Si el código asigna valores distintos en distintos lugares, el panel puede quedar inconsistente.

**Archivos:** `app/Models/PublicationRequest.php`, `app/Services/Publication/PublicationService.php`  
**Fix:** Definir los valores posibles (`pending`, `processing`, `completed`, `failed`, `cancelled`), agregar un cast enum o constantes en el modelo, y validar el campo en todos los puntos de escritura.

---

## Resumen

| Severidad | Cantidad | GAPs |
|-----------|:--------:|------|
| 🔴 Bug crítico | 3 | 01, 02, 03 |
| 🟠 Seguridad | 3 | 04, 05, 06 |
| 🟡 Lógica | 5 | 07, 08, 09, 10, 11 |
| 🔵 UX / Faltante | 4 | 12, 13, 14, 15 |
| ⚪ Deuda técnica | 9 | 16, 17, 18, 19, 20, 21, 22, 23, 24 |
| **Total** | **24** | |

### Orden sugerido para v2.0.0 estable

**Sprint 1 — Rompe cosas (GAP-01, 02, 03, 04, 11):** Los únicos que pueden tirar errores en producción hoy.

**Sprint 2 — Seguridad (GAP-05, 06):** No rompen pero exponen datos.

**Sprint 3 — Comportamiento incorrecto (GAP-07, 08, 09, 10):** El sistema funciona pero hace cosas inesperadas.

**Sprint 4 — UX (GAP-12, 13, 14, 15):** El sistema funciona bien pero la experiencia está incompleta.

**Sprint 5 — Limpieza (GAP-16 al 24):** Deuda técnica. No urgente pero conviene antes de escalar.
