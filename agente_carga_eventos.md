# Agente de Carga de Eventos

Documento operativo para que un agente externo cargue eventos, shows o peñas en Mi Folklore Argentino por API.

Fecha de referencia: 2026-08-04

---

## Objetivo

El agente debe crear eventos por API y dejarlos en estado `draft` para revision editorial humana posterior.

No debe publicar automaticamente salvo instruccion humana explicita.

---

## Base URL

Usar segun entorno:

- Produccion: `https://mifolkloreargentino.com`
- Local: `http://localhost`

Todos los endpoints usan:

```text
/api/v1
```

---

## Endpoint de alta

```text
POST /api/v1/events
```

Este endpoint crea un evento nuevo.

---

## Autenticacion

La API usa **Laravel Sanctum** con **Bearer Token**.

Headers requeridos:

```http
Authorization: Bearer TU_TOKEN_SANCTUM
Accept: application/json
Content-Type: application/json
```

### Restricciones de acceso

- El token debe pertenecer a un usuario autenticado.
- Ese usuario debe tener rol `administrador`.
- Si el token no existe o es invalido, la API responde `401 Unauthorized`.
- Si el token es valido pero el usuario no tiene permisos, la API responde `403 Forbidden`.

---

## Como generar el token Sanctum

Hoy no existe un endpoint dedicado de login para este flujo del agente. La forma operativa actual es generar un token Sanctum manualmente para un usuario administrador.

### Opcion recomendada: Laravel Tinker

```bash
docker compose exec app php artisan tinker
```

Luego:

```php
$user = App\Models\User::where('email', 'admin@tu-dominio.com')->first();
$token = $user->createToken('agente-eventos')->plainTextToken;
$token;
```

Ese valor devuelto es el token que debe usar el agente.

### Validacion previa recomendada

```php
$user = App\Models\User::where('email', 'admin@tu-dominio.com')->first();
$user->getRoleNames();
```

Debe aparecer `administrador`.

---

## Comportamiento editorial

### Estado recomendado para el agente

Usar siempre:

```json
"editorial_status": "draft"
```

### Comportamiento por defecto

Si el agente no envia `editorial_status`, el sistema guarda el evento como:

```text
draft
```

### Estados permitidos hoy

- `draft`
- `published`
- `archived`

### Importante

La API fue ajustada para que un usuario admin no publique automaticamente un evento por el solo hecho de ser admin.

---

## Endpoints de consulta

```text
GET /api/v1/events
GET /api/v1/events/{id}
```

Filtros soportados en listado:

- `editorial_status`
- `province_id`
- `event_type`
- `published_from`
- `published_to`
- `per_page`

Si no se envia `editorial_status`, el listado devuelve eventos `published`.

---

## Payload de alta

La API soporta dos formatos:

1. **Formato canonico recomendado**
2. **Formato legacy** por compatibilidad con `shows`

Para integraciones nuevas, usar siempre el formato canonico.

### Campos obligatorios en formato canonico

- `title`
- `start_at`

### Campos obligatorios en formato legacy

- `show`
- `fecha`

### Campos opcionales recomendados

- `body`
- `subtitle`
- `excerpt`
- `slug`
- `end_at`
- `event_type`
- `modality`
- `timezone`
- `province_id`
- `city`
- `address`
- `ticket_url`
- `price_text`
- `is_free`
- `capacity`
- `editorial_status`
- `published_at`
- `seo_title`
- `meta_description`
- `interprete_id`
- `interprete_secundarios`

### Campos administrativos opcionales

- `created_by`
- `user_id`

Normalmente no hace falta enviarlos. Si no se envian, el sistema toma por defecto al usuario autenticado como autor.

---

## Reglas de validacion importantes

- `title` maximo 255 caracteres.
- `show` maximo 255 caracteres.
- `slug` maximo 255 caracteres.
- `subtitle` maximo 255 caracteres.
- `excerpt` maximo 1000 caracteres.
- `meta_description` maximo 320 caracteres.
- `start_at` o `fecha` deben ser fechas validas.
- `end_at` debe ser posterior a `start_at`.
- `editorial_status` solo admite `draft`, `published` o `archived`.
- `province_id` debe existir en `provincias`.
- `interprete_id` debe existir si se envia.
- `interprete_secundarios` debe ser array de IDs existentes.
- `ticket_url` debe ser URL valida.

---

## Ejemplo JSON recomendado

```json
{
  "title": "Peña folklorica en Cordoba",
  "subtitle": "Noche con artistas locales y repertorio tradicional",
  "excerpt": "Encuentro folklorico con musica en vivo, danza y espacio para artistas invitados.",
  "body": "<p>La peña reunira a distintos artistas y agrupaciones en una noche orientada al cancionero popular...</p>",
  "start_at": "2026-08-20T21:00:00-03:00",
  "end_at": "2026-08-21T01:00:00-03:00",
  "event_type": "pena",
  "modality": "presencial",
  "timezone": "America/Argentina/Buenos_Aires",
  "province_id": 6,
  "city": "Cordoba",
  "address": "Av. Colon 1200",
  "ticket_url": "https://example.com/entradas/pena-cordoba",
  "price_text": "Entrada general $12000",
  "is_free": false,
  "editorial_status": "draft",
  "seo_title": "Peña folklorica en Cordoba con artistas locales",
  "meta_description": "Consulta los datos clave de esta peña folklorica en Cordoba con fecha, ciudad, tipo de evento y propuesta artistica.",
  "interprete_id": 25,
  "interprete_secundarios": []
}
```

---

## Ejemplo curl completo

```bash
curl -X POST "https://mifolkloreargentino.com/api/v1/events" \
  -H "Authorization: Bearer TU_TOKEN_SANCTUM" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Peña folklorica en Cordoba",
    "subtitle": "Noche con artistas locales y repertorio tradicional",
    "excerpt": "Encuentro folklorico con musica en vivo, danza y espacio para artistas invitados.",
    "body": "<p>La peña reunira a distintos artistas y agrupaciones en una noche orientada al cancionero popular...</p>",
    "start_at": "2026-08-20T21:00:00-03:00",
    "end_at": "2026-08-21T01:00:00-03:00",
    "event_type": "pena",
    "modality": "presencial",
    "timezone": "America/Argentina/Buenos_Aires",
    "province_id": 6,
    "city": "Cordoba",
    "address": "Av. Colon 1200",
    "ticket_url": "https://example.com/entradas/pena-cordoba",
    "price_text": "Entrada general $12000",
    "is_free": false,
    "editorial_status": "draft",
    "seo_title": "Peña folklorica en Cordoba con artistas locales",
    "meta_description": "Consulta los datos clave de esta peña folklorica en Cordoba con fecha, ciudad, tipo de evento y propuesta artistica.",
    "interprete_id": 25
  }'
```

---

## Respuesta esperada

Si la creacion sale bien:

- Status HTTP: `201 Created`
- Devuelve el evento creado en JSON

Campos esperables:

- `id`
- `title`
- `slug`
- `start_at`
- `end_at`
- `province_id`
- `city`
- `event_type`
- `editorial_status`
- `published_at`
- `created_by`

---

## Flujo recomendado para el agente

### Paso 1

Construir el payload canonico con:

- `title`
- `subtitle`
- `excerpt`
- `body`
- `start_at`
- `end_at`
- `event_type`
- `province_id`
- `city`
- `address`
- `ticket_url`
- `price_text`
- `is_free`
- `editorial_status`
- `seo_title`
- `meta_description`

### Paso 2

Crear el evento en `draft`:

```text
POST /api/v1/events
```

### Paso 3

Guardar el `id` devuelto por la API para futuras actualizaciones.

### Paso 4

No publicar automaticamente.

---

## Errores frecuentes

### `401 Unauthorized`

Causas probables:

- falta header `Authorization`
- token incorrecto
- token vencido o revocado

### `403 Forbidden`

Causas probables:

- el token pertenece a un usuario sin rol `administrador`

### `422 Unprocessable Entity`

Causas probables:

- falta `title` o `show`
- falta `start_at` o `fecha`
- `end_at` anterior a `start_at`
- `editorial_status` invalido
- `province_id` inexistente
- IDs de interpretes inexistentes

### `500 Internal Server Error`

Causas probables:

- error interno del servidor
- problema de infraestructura
- error de transaccion

---

## Actualizacion posterior de un evento

Si el agente necesita corregir o ampliar un evento ya creado:

```text
PUT   /api/v1/events/{id}
PATCH /api/v1/events/{id}
```

Ejemplo:

```json
{
  "excerpt": "Version actualizada de la bajada editorial.",
  "body": "<p>Contenido actualizado.</p>",
  "editorial_status": "draft",
  "price_text": "Entrada general $14000"
}
```

---

## Resumen operativo corto

1. Autenticarse con `Bearer Token` de Sanctum de un usuario `administrador`.
2. Crear el evento con `POST /api/v1/events`.
3. Enviar contenido en formato canonico JSON.
4. Dejar `editorial_status` en `draft`.
5. No publicar automaticamente.

---

## Observacion final

Al 2026-08-04, el flujo recomendado para agentes en `events` es:

- ingesta automatica por API
- guardado en `draft`
- revision humana posterior

No existe todavia un endpoint exclusivo para agentes ni un estado editorial `para_revision`.
