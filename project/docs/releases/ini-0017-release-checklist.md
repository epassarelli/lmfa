# INI-0017 — Checklist de release: modernización de Mitos y Leyendas

## Alcance
Release independiente sobre el módulo Mito existente. No se crea módulo paralelo.

Incluye:
- content_type, excerpt, region, SEO e image_alt;
- compatibilidad legacy de foto/visitas/estado;
- API moderna y media transversal;
- backoffice actualizado;
- SEO y Article schema grounded;
- auditor mfa:myths:audit;
- plantilla editorial.

## Migración
2026_08_31_235500_modernize_mitos_editorial_fields.php

Todos los campos nuevos son opcionales. El body legacy mito se conserva intacto.
Las fechas cero legacy se normalizan a NULL antes de reconstruir la tabla.

## Smoke
- índice y detalle público;
- alta/edición backoffice;
- API GET/POST/PUT;
- user_id y visitas enviados por cliente -> 422;
- slug duplicado 422;
- body sin H1;
- Article schema sólo con datos persistidos;
- media propia/legacy/fallback;
- tipo y región opcionales.
- contenido inactivo -> 404 público;
- estado inactivo y autor original preservados al editar en backoffice.

## Auditor
php artisan mfa:myths:audit --active
php artisan mfa:myths:audit --active --csv=storage/app/audits/mitos-produccion.csv

## Gate
CI verde, migración revisada, smoke aprobado y auditor real de producción.
