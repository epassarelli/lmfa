# INI-0015 — Checklist de release: modernización de Biografías

## Alcance

Release independiente de Artistas/Biografías.

Incluye:
- campos editoriales modernos;
- SEO persistente;
- media transversal;
- tipo solista/grupo;
- API create/update moderna;
- backoffice;
- frontend/schema;
- auditor read-only;
- limpieza de wording legacy Shows -> Eventos.

No incluye:
- saneamiento masivo de las 444 biografías;
- eliminación física de rutas legacy /shows;
- Recetas, Mitos ni Letras.

## Migración

`2026_08_31_233000_modernize_interpretes_editorial_fields.php`

Además se ejecuta `2026_08_31_233100_widen_interpretes_legacy_columns.php`, que amplía nombres, slugs, correos y URLs legacy a 255 caracteres para alinear el esquema con las validaciones modernas. Ese ensanchamiento no se revierte automáticamente porque reducirlo podría truncar datos válidos creados después del release.

Agrega:
- artist_type
- excerpt
- seo_title
- meta_description
- image_alt
- web

Todos nullable. No elimina ni renombra columnas existentes.

Antes del deploy:

```bash
php artisan migrate:status
php artisan migrate --pretend
```

Producción:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan view:cache
```

## Smoke API

- GET /api/v1/artists
- GET /api/v1/artists/{id}
- POST sin user_id/estado -> autor autenticado + estado inactivo
- user_id enviado por cliente -> 422; la autoría no puede suplantarse
- slug duplicado -> 422
- PUT parcial
- imagen externa/interna si se prueba media
- body sin H1

## Smoke backoffice

Crear/editar artista sin publicar:
- nombre
- slug
- tipo solista/grupo
- excerpt
- biografía
- SEO
- image_alt
- foto opcional JPEG/PNG/WebP
- web/redes
- estado

Al editar como administrador, comprobar que `estado=0` permanece inactivo y no se reactiva por el solo hecho de guardar.

## Smoke frontend

Validar:
- hub de artista
- /biografia
- índice de biografías
- letra A-Z
- schema Person para soloist
- schema MusicGroup para group/legacy sin tipo
- no FAQPage artificial
- canonical/meta/social image
- media propia/legacy/fallback
- artista inactivo -> 404 tanto en el hub como en `/biografia`
- wording visible Eventos, no Shows, salvo URLs legacy.

## Auditoría

Después del deploy:

```bash
php artisan mfa:artists:audit --active
php artisan mfa:artists:audit --active --csv=storage/app/audits/artistas-produccion.csv
```

No modifica datos.

## Rollback

Código:
- revertir merge del release.

Esquema, sólo si fuera necesario:

```bash
php artisan migrate:rollback --path=database/migrations/2026_08_31_233000_modernize_interpretes_editorial_fields.php --force
```

## Gate

Cerrar INI-0015 sólo si:
- CI específico verde;
- migración revisada;
- smoke API/backoffice/frontend aprobado;
- auditor producción ejecutado;
- no regresiones de media/SEO;
- saneamiento editorial queda separado del release técnico.
