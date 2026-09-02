# Agente de Carga de Enciclopedia

Documento operativo para que un agente externo cargue articulos en la Enciclopedia del Folklore Argentino por API.

Fecha de referencia: 2026-08-04

---

## Objetivo

El agente debe crear articulos en la Enciclopedia mediante API y dejarlos en estado `draft` para revision editorial humana posterior.

No debe publicar articulos automaticamente salvo instruccion humana explicita.

---

## Base URL

Usar una de estas segun entorno:

- Produccion: `https://mifolkloreargentino.com`
- Local: `http://localhost`

Todos los endpoints del modulo usan el prefijo:

```text
/api/v1
```

---

## Endpoint de alta

```text
POST /api/v1/knowledge-articles
```

Este endpoint crea un articulo nuevo de Enciclopedia.

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

Hoy el proyecto no expone un endpoint especifico de login para este flujo de agente. La forma operativa actual es generar manualmente un token Sanctum para un usuario administrador.

### Opcion recomendada: Laravel Tinker

Ejecutar en el contenedor/app:

```bash
docker compose exec app php artisan tinker
```

Luego:

```php
$user = App\Models\User::where('email', 'admin@tu-dominio.com')->first();
$token = $user->createToken('agente-enciclopedia')->plainTextToken;
$token;
```

Ese valor devuelto es el token que debe usar el agente.

### Validacion previa recomendada

Antes de crear el token, confirmar que el usuario:

- existe
- tiene rol `administrador`

Ejemplo:

```php
$user = App\Models\User::where('email', 'admin@tu-dominio.com')->first();
$user->getRoleNames();
```

Si no aparece `administrador`, ese usuario no va a poder escribir en la API.

---

## Comportamiento editorial

### Estado recomendado para el agente

Usar siempre:

```json
"editorial_status": "draft"
```

### Comportamiento por defecto

Si el agente no envia `editorial_status`, el sistema guarda el articulo como:

```text
draft
```

### Estados permitidos hoy

- `draft`
- `published`
- `archived`

### Importante

No existe hoy un estado `para_revision` o `in_review`.

Si se necesita ese flujo a futuro, debe implementarse aparte.

---

## Endpoint para consultar familias

Antes de crear un articulo, el agente debe consultar las familias disponibles para obtener el `knowledge_category_id` correcto.

```text
GET /api/v1/knowledge-categories
```

### Recomendacion

No inventar `knowledge_category_id`. Siempre obtenerlo por API o desde una tabla de referencia validada.

### Identificador estable recomendado

Para automatizaciones externas, el identificador mas estable es el `slug`.

El endpoint de alta acepta hoy cualquiera de estas variantes:

- `knowledge_category_id`
- `knowledge_category_slug`
- `knowledge_category_name`

La recomendacion operativa sigue siendo:

1. consultar `GET /api/v1/knowledge-categories`
2. resolver la familia por `slug`
3. enviar preferentemente `knowledge_category_slug`

De ese modo se evita acoplar la planilla o el Apps Script a IDs numericos que pueden variar entre entornos.

---

## Familias actuales esperables

Al 2026-08-04, las familias activas son:

- `ritmos`
- `danzas`
- `instrumentos`
- `regiones`
- `provincias`
- `historia`
- `tradiciones`
- `cancionero`
- `aprender`

---

## Payload de alta

### Campos obligatorios

- una categoria valida, enviada como:
  - `knowledge_category_id` integer, o
  - `knowledge_category_slug` string, o
  - `knowledge_category_name` string
- `title` string
- `slug` string
- `body` string

### Campos opcionales recomendados

- `excerpt`
- `editorial_status`
- `published_at`
- `last_verified_at`
- `image_alt`
- `seo_title`
- `meta_description`
- `primary_keyword`
- `secondary_keywords`
- `featured_image_path`
- `interprete_ids`
- `cancion_ids`
- `album_ids`
- `festival_ids`
- `event_ids`
- `provincia_ids`
- `related_article_ids`

### Campos opcionales administrativos

- `author_id`
- `reviewed_by`

Normalmente no hace falta enviarlos. Si no se envian, el sistema toma por defecto el usuario autenticado como autor.

---

## Reglas de validacion importantes

- `slug` debe ser unico dentro de la misma familia.
- `title` maximo 255 caracteres.
- `slug` maximo 255 caracteres.
- `excerpt` maximo 1000 caracteres.
- `meta_description` maximo 320 caracteres.
- `secondary_keywords` maximo 1000 caracteres.
- `editorial_status` solo admite `draft`, `published` o `archived`.
- Los IDs de relaciones deben existir realmente en sus tablas.

---

## Ejemplo JSON recomendado

```json
{
  "knowledge_category_slug": "ritmos",
  "title": "Chacarera: origen, ritmo y claves para entenderla",
  "slug": "chacarera-origen-ritmo-y-claves",
  "excerpt": "Guia editorial para comprender que es la chacarera, como suena y por que ocupa un lugar central en el folklore argentino.",
  "body": "<p>La chacarera es uno de los ritmos mas representativos del folklore argentino...</p>",
  "editorial_status": "draft",
  "published_at": null,
  "last_verified_at": "2026-08-04T12:00:00-03:00",
  "image_alt": "Pareja bailando chacarera en una peña folklorica",
  "seo_title": "Chacarera: origen, ritmo y claves para entenderla",
  "meta_description": "Descubri que es la chacarera, cual es su origen, como se distingue de otros ritmos y por que es clave en el folklore argentino.",
  "primary_keyword": "chacarera",
  "secondary_keywords": "ritmos del folklore argentino, musica folklorica argentina",
  "interprete_ids": [],
  "cancion_ids": [],
  "album_ids": [],
  "festival_ids": [],
  "event_ids": [],
  "provincia_ids": [22],
  "related_article_ids": []
}
```

---

## Ejemplo curl completo

```bash
curl -X POST "https://mifolkloreargentino.com/api/v1/knowledge-articles" \
  -H "Authorization: Bearer TU_TOKEN_SANCTUM" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "knowledge_category_slug": "ritmos",
    "title": "Chacarera: origen, ritmo y claves para entenderla",
    "slug": "chacarera-origen-ritmo-y-claves",
    "excerpt": "Guia editorial para comprender que es la chacarera, como suena y por que ocupa un lugar central en el folklore argentino.",
    "body": "<p>La chacarera es uno de los ritmos mas representativos del folklore argentino...</p>",
    "editorial_status": "draft",
    "last_verified_at": "2026-08-04T12:00:00-03:00",
    "image_alt": "Pareja bailando chacarera en una peña folklorica",
    "seo_title": "Chacarera: origen, ritmo y claves para entenderla",
    "meta_description": "Descubri que es la chacarera, cual es su origen, como se distingue de otros ritmos y por que es clave en el folklore argentino.",
    "primary_keyword": "chacarera",
    "secondary_keywords": "ritmos del folklore argentino, musica folklorica argentina",
    "provincia_ids": [22]
  }'
```

---

## Respuesta esperada

Si la creacion sale bien:

- Status HTTP: `201 Created`
- Respuesta JSON con el articulo creado

Campos esperables en la respuesta:

- `id`
- `knowledge_category_id`
- `title`
- `slug`
- `excerpt`
- `body`
- `editorial_status`
- `published_at`
- `last_verified_at`
- relaciones asociadas

---

## Flujo recomendado para el agente

### Paso 1

Consultar familias:

```text
GET /api/v1/knowledge-categories
```

### Paso 2

Elegir la familia correcta y capturar `knowledge_category_id`.

### Paso 3

Construir el payload con:

- `title`
- `slug`
- `excerpt`
- `body`
- `seo_title`
- `meta_description`
- `primary_keyword`
- `secondary_keywords`
- relaciones verificadas si existen

### Paso 4

Crear el articulo en `draft`:

```text
POST /api/v1/knowledge-articles
```

### Paso 5

Guardar el `id` devuelto por la API para futuras actualizaciones o publicacion manual.

### Paso 6

No publicar automaticamente.

La publicacion humana posterior usa:

```text
POST /api/v1/knowledge-articles/{id}/publish
```

pero no debe ser usada por el agente salvo instruccion humana directa.

---

## Reglas editoriales recomendadas para el agente

- Crear siempre en `draft`.
- No enviar `published` por defecto.
- Usar slugs limpios, en minusculas y con guiones.
- No inventar relaciones por IDs si no fueron verificadas.
- Enviar `meta_description` concreta, informativa y no generica.
- Enviar `body` en HTML simple y limpio cuando sea posible.
- Evitar contenido duplicado dentro de una misma familia.

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

- falta `knowledge_category_id`
- falta `title`
- falta `slug`
- falta `body`
- `slug` duplicado dentro de la misma familia
- IDs de relaciones inexistentes
- `editorial_status` invalido

### `500 Internal Server Error`

Causas probables:

- error interno del servidor
- datos de imagen no resolubles
- problema transaccional o de infraestructura

---

## Actualizacion posterior de un articulo

Si el agente necesita corregir o ampliar un articulo ya creado:

```text
PUT   /api/v1/knowledge-articles/{id}
PATCH /api/v1/knowledge-articles/{id}
```

Ejemplo:

```json
{
  "excerpt": "Version actualizada de la bajada editorial.",
  "body": "<p>Contenido actualizado.</p>",
  "editorial_status": "draft",
  "last_verified_at": "2026-08-04T12:30:00-03:00"
}
```

---

## Publicacion manual posterior

Solo si un humano lo indica expresamente:

```text
POST /api/v1/knowledge-articles/{id}/publish
```

Para volver a borrador:

```text
POST /api/v1/knowledge-articles/{id}/unpublish
```

---

## Resumen operativo corto

Si el agente solo necesita la instruccion minima:

1. Autenticarse con `Bearer Token` de Sanctum de un usuario `administrador`.
2. Consultar `GET /api/v1/knowledge-categories`.
3. Resolver la familia por `slug`.
4. Crear el articulo con `POST /api/v1/knowledge-articles`.
5. Enviar el contenido en JSON.
6. Dejar `editorial_status` en `draft`.
7. No publicar automaticamente.

### Error estructurado por categoria

Si la familia enviada no existe, esta inactiva o falta cuando es obligatoria, la API responde:

- HTTP `422`
- `code: BLOQUEADO_CATEGORIA`

Esto debe tratarse como error deterministico de validacion y no como fallo reintentable.

---

## Observacion final

Al 2026-08-04, el flujo soportado y recomendado para agentes es:

- ingesta automatica por API
- guardado en `draft`
- revision humana posterior

No existe todavia un endpoint dedicado exclusivo para agentes ni un estado editorial `para_revision`.
