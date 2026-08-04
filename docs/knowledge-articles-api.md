# API de Enciclopedia

## Base

- Lectura y escritura bajo `/api/v1`
- Middleware general: `auth:sanctum`
- Escritura restringida a `role:administrador`

## Endpoints

```text
GET    /api/v1/knowledge-articles
POST   /api/v1/knowledge-articles
GET    /api/v1/knowledge-articles/{id}
PUT    /api/v1/knowledge-articles/{id}
PATCH  /api/v1/knowledge-articles/{id}
DELETE /api/v1/knowledge-articles/{id}
POST   /api/v1/knowledge-articles/{id}/publish
POST   /api/v1/knowledge-articles/{id}/unpublish
GET    /api/v1/knowledge-categories
```

## Ejemplo de alta

```json
{
  "knowledge_category_id": 1,
  "title": "Chacarera",
  "slug": "chacarera",
  "excerpt": "Guía breve sobre el ritmo y su contexto cultural.",
  "body": "<p>Contenido enriquecido del artículo.</p>",
  "editorial_status": "draft",
  "published_at": null,
  "last_verified_at": "2026-08-04T10:30:00-03:00",
  "image_alt": "Pareja bailando chacarera en una peña",
  "seo_title": "Chacarera: origen, ritmo y claves para entenderla",
  "meta_description": "Qué es la chacarera, cómo se distingue y por qué ocupa un lugar central en el folklore argentino.",
  "primary_keyword": "chacarera",
  "secondary_keywords": "ritmo folklórico, folklore argentino",
  "interprete_ids": [12, 34],
  "cancion_ids": [55],
  "album_ids": [9],
  "festival_ids": [4],
  "event_ids": [8],
  "provincia_ids": [3],
  "related_article_ids": [101, 102]
}
```

## Ejemplo de actualización

```json
{
  "excerpt": "Versión actualizada de la bajada editorial.",
  "body": "<p>Contenido actualizado.</p>",
  "editorial_status": "published",
  "published_at": "2026-08-04T11:00:00-03:00",
  "last_verified_at": "2026-08-04T11:00:00-03:00",
  "related_article_ids": [102, 103]
}
```

## Notas

- La unicidad del slug se valida dentro de cada familia.
- Crear un artículo no lo publica automáticamente si llega como borrador.
- `DELETE` archiva el contenido y aplica soft delete.
- No se implementó relación estructurada con regiones porque no existe todavía un modelo canónico equivalente en el repositorio.
