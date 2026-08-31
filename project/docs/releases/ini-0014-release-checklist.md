# INI-0014 — Checklist de release: modernización de Festivales

## Alcance

Release independiente del módulo Festivales. No incluye Biografías ni upgrades funcionales de otros módulos.

Contrato funcional:
- Festival representa identidad estable, historia, ubicación habitual, mes, SEO, media y relaciones.
- Fechas concretas, horarios, sede, grilla, entradas y programación pertenecen a Eventos/ediciones relacionadas.

## Cambios de esquema

Migración:
- `2026_08_31_210000_add_image_alt_to_festivales.php`
- agrega `festivales.image_alt` nullable.
- no transforma ni elimina datos existentes.
- rollback disponible mediante `down()`.

Antes del deploy:

```bash
php artisan migrate:status
php artisan migrate --pretend
```

En producción:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan view:cache
```

No se requiere Composer ni NPM mientras composer.lock/package-lock no cambien.

## Smoke público

Validar:

1. Home de Festivales.
2. Filtro por provincia.
3. Filtro por mes.
4. Landing provincia + mes.
5. Detalle de Festival legacy con datos incompletos.
6. Detalle de Festival moderno con provincia/localidad/mes.
7. Festival con imagen propia.
8. Festival con imagen relacionada.
9. Festival con fallback.
10. Noticias relacionadas: sólo publicadas.
11. Eventos relacionados: sólo publicados y futuros.
12. Artistas relacionados: sólo activos.
13. Enciclopedia relacionada: sólo visible/publicada.

Comprobar:
- H1 único generado desde title.
- body sin H1.
- canonical correcto.
- meta title y description correctos.
- og:image / twitter:image efectivos.
- JSON-LD incluye imagen efectiva.
- ausencia de placeholders grises.

## Smoke backoffice

Crear o editar un Festival de prueba sin publicarlo.

Validar:
- title / slug.
- excerpt / body.
- province_id.
- locality_id.
- mes_id.
- seo_title.
- meta_description.
- image_alt.
- imagen JPEG/PNG/WebP hasta 5 MB.
- relaciones con Noticias, Eventos, Artistas y Enciclopedia.
- status / published_at.

No publicar contenido de prueba innecesariamente.

## Smoke API

Con token administrador:
- GET /api/v1/festivals.
- GET /api/v1/festivals/{id}.
- POST de Festival draft sin user_id/status: debe tomar autor autenticado y draft.
- slug duplicado: debe devolver 422.
- actualización de relaciones.
- FEATURED_IMAGE_URL / image_alt si se prueba media externa.

## Auditoría editorial read-only

Después del deploy:

```bash
php artisan mfa:festivals:audit --published
php artisan mfa:festivals:audit --published --csv=storage/app/audits/festivales-produccion.csv
```

El comando puntúa:
- identidad;
- excerpt;
- cuerpo;
- provincia;
- localidad;
- mes;
- seo_title;
- meta_description;
- imagen efectiva;
- relaciones.

No modifica registros.

El inventario actual de referencia previo al release es de 30 Festivales publicados según el auditor transversal de imágenes. La cola de expansión editorial contiene 30 candidatos adicionales: 25 LISTO_REVISION y 5 PENDIENTE_INVESTIGACION. No mezclar ambos universos.

## Rollout

1. Backup de BD.
2. Confirmar PR/CI verde.
3. Merge explícitamente aprobado.
4. Pull de main en producción.
5. Migración.
6. Limpiar caches.
7. Smoke público.
8. Smoke backoffice/API controlado.
9. Ejecutar auditor de Festivales.
10. Revisar logs.

## Rollback

Código:
- revertir el merge/release de INI-0014 y desplegar main revertido.

Esquema, sólo si fuera necesario:
```bash
php artisan migrate:rollback --path=database/migrations/2026_08_31_210000_add_image_alt_to_festivales.php --force
```

La columna es nullable y puede permanecer sin riesgo si se revierte sólo el código.

## Gate

BL-0014I sólo puede aprobarse si:
- CI de Festivales verde.
- migración y rollback revisados.
- smoke público aprobado.
- backoffice/API operativos.
- auditoría de producción ejecutada.
- no hay regresiones de SEO/media.
- no hay cambios de Biografías incluidos.
