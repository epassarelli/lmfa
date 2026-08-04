# Agente de Carga de Noticias

Documento operativo para que un agente externo cargue noticias en Mi Folklore Argentino por API.

Fecha de referencia: 2026-08-04

---

## Objetivo

El agente debe crear noticias por API y dejarlas en estado `draft` para revision editorial posterior.

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
POST /api/v1/news
```

Este endpoint crea una noticia nueva.

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
$token = $user->createToken('agente-noticias')->plainTextToken;
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

Si el agente no envia `editorial_status`, el sistema guarda la noticia como:

```text
draft
```

### Estados permitidos hoy

- `draft`
- `published`
- `archived`

### Importante

La API fue ajustada para que un usuario admin no publique automaticamente una noticia por el solo hecho de ser admin.

---

## Endpoint para consultar noticias

```text
GET /api/v1/news
GET /api/v1/news/{id}
```

Filtros soportados en listado:

- `categoria_id`
- `editorial_status`
- `published_from`
- `published_to`

---

## Payload de alta

La API soporta dos formatos:

1. **Formato canonico recomendado**
2. **Formato legacy** por compatibilidad

Para integraciones nuevas, usar siempre el formato canonico.

### Campos obligatorios en formato canonico

- `title`
- `body`
- `categoria_id`

### Campos obligatorios en formato legacy

- `titulo`
- `noticia`
- `categoria_id`

### Campos opcionales recomendados

- `slug`
- `subtitle`
- `excerpt`
- `editorial_status`
- `published_at`
- `seo_title`
- `meta_description`
- `featured_image_path`
- `interprete_id`
- `interprete_principal_id`
- `interprete_secundarios`

### Campos administrativos opcionales

- `created_by`
- `approved_by`

Normalmente no hace falta enviarlos. Si no se envian, el sistema toma por defecto al usuario autenticado como autor.

---

## Reglas de validacion importantes

- `title` maximo 255 caracteres.
- `titulo` maximo 255 caracteres.
- `slug` maximo 255 caracteres.
- `subtitle` maximo 255 caracteres.
- `excerpt` maximo 1000 caracteres.
- `meta_description` maximo 320 caracteres.
- `categoria_id` debe existir.
- `editorial_status` solo admite `draft`, `published` o `archived`.
- `interprete_id` e `interprete_principal_id` deben existir si se envian.
- `interprete_secundarios` debe ser array de IDs existentes.

---

## Ejemplo JSON recomendado

```json
{
  "title": "Abel Pintos anuncio nuevas fechas en el interior",
  "slug": "abel-pintos-anuncio-nuevas-fechas-en-el-interior",
  "subtitle": "La agenda incluye recitales en Cordoba, Tucuman y Mendoza",
  "excerpt": "Resumen breve sobre el anuncio de nuevas fechas y el impacto en la agenda folklorica del mes.",
  "body": "<p>Abel Pintos confirmo una nueva serie de presentaciones en distintas provincias...</p>",
  "categoria_id": 1,
  "editorial_status": "draft",
  "published_at": null,
  "seo_title": "Abel Pintos anuncio nuevas fechas en el interior del pais",
  "meta_description": "Conoce las nuevas fechas anunciadas por Abel Pintos en el interior y que lugar ocupan dentro de la agenda reciente del folklore argentino.",
  "interprete_id": 25,
  "interprete_secundarios": []
}
```

---

## Ejemplo curl completo

```bash
curl -X POST "https://mifolkloreargentino.com/api/v1/news" \
  -H "Authorization: Bearer TU_TOKEN_SANCTUM" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Abel Pintos anuncio nuevas fechas en el interior",
    "slug": "abel-pintos-anuncio-nuevas-fechas-en-el-interior",
    "subtitle": "La agenda incluye recitales en Cordoba, Tucuman y Mendoza",
    "excerpt": "Resumen breve sobre el anuncio de nuevas fechas y el impacto en la agenda folklorica del mes.",
    "body": "<p>Abel Pintos confirmo una nueva serie de presentaciones en distintas provincias...</p>",
    "categoria_id": 1,
    "editorial_status": "draft",
    "seo_title": "Abel Pintos anuncio nuevas fechas en el interior del pais",
    "meta_description": "Conoce las nuevas fechas anunciadas por Abel Pintos en el interior y que lugar ocupan dentro de la agenda reciente del folklore argentino.",
    "interprete_id": 25
  }'
```

---

## Respuesta esperada

Si la creacion sale bien:

- Status HTTP: `201 Created`
- Devuelve la noticia creada en JSON

Campos esperables:

- `id`
- `title`
- `slug`
- `body`
- `categoria_id`
- `editorial_status`
- `published_at`
- `created_by`

---

## Flujo recomendado para el agente

### Paso 1

Obtener el `categoria_id` correcto desde catalogo interno o valor previamente validado por humanos.

### Paso 2

Construir el payload canonico con:

- `title`
- `slug`
- `subtitle`
- `excerpt`
- `body`
- `categoria_id`
- `editorial_status`
- `seo_title`
- `meta_description`

### Paso 3

Crear la noticia en `draft`:

```text
POST /api/v1/news
```

### Paso 4

Guardar el `id` devuelto por la API para futuras actualizaciones.

### Paso 5

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

- falta `title` o `titulo`
- falta `body` o `noticia`
- falta `categoria_id`
- `editorial_status` invalido
- IDs de interpretes inexistentes

### `500 Internal Server Error`

Causas probables:

- error interno del servidor
- problema de infraestructura
- error de imagen o transaccion

---

## Actualizacion posterior de una noticia

Si el agente necesita corregir o ampliar una noticia ya creada:

```text
PUT   /api/v1/news/{id}
PATCH /api/v1/news/{id}
```

Ejemplo:

```json
{
  "excerpt": "Version actualizada de la bajada editorial.",
  "body": "<p>Contenido actualizado.</p>",
  "editorial_status": "draft"
}
```

---

## Resumen operativo corto

1. Autenticarse con `Bearer Token` de Sanctum de un usuario `administrador`.
2. Crear la noticia con `POST /api/v1/news`.
3. Enviar contenido en formato canonico JSON.
4. Dejar `editorial_status` en `draft`.
5. No publicar automaticamente.

---

## Observacion final

Al 2026-08-04, el flujo recomendado para agentes en Noticias es:

- ingesta automatica por API
- guardado en `draft`
- revision humana posterior

No existe todavia un endpoint exclusivo para agentes ni un estado editorial `para_revision`.
