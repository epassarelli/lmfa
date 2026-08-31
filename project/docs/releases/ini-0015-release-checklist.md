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
