# INI-0016 — Checklist de release: modernización de Recetas

## Alcance
Release independiente del módulo Recetas/Comidas.

Incluye:
- estructura opcional de ingredientes/pasos;
- tiempos, porciones y región opcionales;
- SEO persistente;
- image_alt;
- API moderna;
- backoffice;
- Recipe schema condicional;
- auditor read-only.

No incluye saneamiento masivo de las ~808 recetas.

## Migración
2026_08_31_234500_modernize_comidas_editorial_fields.php

Agrega campos modernos y hace compatible el legacy:
- foto nullable;
- visitas default 0;
- estado default 0.

Rollback elimina sólo campos nuevos; no vuelve a imponer restricciones legacy incompatibles.

## Smoke
API:
- GET/POST/PUT foods
- slug duplicado 422
- alta sin publicar/estado/visitas
- imagen externa/interna
- body sin H1

Backoffice:
- ingredientes y pasos multilínea
- tiempos/porciones/región
- SEO/image_alt
- foto opcional

Frontend:
- legacy sigue visible
- structured recipe emite Recipe schema
- legacy sin estructura NO emite Recipe schema
- canonical/meta/social image
- media fallback válida

## Auditor
php artisan mfa:recipes:audit --active
php artisan mfa:recipes:audit --active --csv=storage/app/audits/recetas-produccion.csv

## Gate
Cerrar sólo con CI verde, smoke aprobado y auditor real de producción.