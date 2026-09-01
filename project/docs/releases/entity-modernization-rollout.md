# Rollout por oleadas — Modernización de entidades MFA

## Principio

Cada entidad se despliega como release independiente. No agrupar migraciones de Biografías, Recetas, Mitos y Discografía en una única ventana.

Orden recomendado:

1. Biografías / Artistas
2. Recetas
3. Mitos y Leyendas
4. Integración editorial Artista + Receta + Mito
5. Discografía / Cancionero

La integración editorial se despliega después de los tres backends que consume.

## Precheck común

En cada release:

```bash
git status
git branch --show-current
php artisan migrate:status
php artisan migrate --pretend
```

Confirmar:
- PR/CI verde;
- backup de BD disponible;
- branch correcta;
- rollback documentado;
- sin cambios de Composer/NPM no previstos.

## Oleada 1 — Biografías

PR #9 — feature/biographies-modernization

Migraciones:
- 2026_08_31_233000_modernize_interpretes_editorial_fields.php
- 2026_08_31_233100_widen_interpretes_legacy_columns.php

Después del deploy:

```bash
php artisan optimize:clear
php artisan view:cache
php artisan mfa:artists:audit --active
```

Smoke:
- índice biografías;
- hub artista;
- /biografia;
- alta/edición backend;
- GET/POST/PUT API;
- Person/MusicGroup schema;
- media propia/legacy/fallback.

No habilitar todavía ENVIAR_API=S para Artista hasta completar smoke.

## Oleada 2 — Recetas

PR #10 — feature/recipes-modernization

Migración:
- 2026_08_31_234500_modernize_comidas_editorial_fields.php

Después:

```bash
php artisan optimize:clear
php artisan view:cache
php artisan mfa:recipes:audit --active
```

Smoke:
- listado/detalle;
- receta legacy;
- receta estructurada;
- Recipe JSON-LD sólo con datos reales;
- alta/edición;
- API create/update;
- media.

No habilitar actualización masiva. Primero revisar auditor y lote P1.

## Oleada 3 — Mitos

PR #11 — feature/myths-modernization

Migración:
- 2026_08_31_235500_modernize_mitos_editorial_fields.php

Después:

```bash
php artisan optimize:clear
php artisan view:cache
php artisan mfa:myths:audit --active
```

Smoke:
- índice/detalle;
- myth/legend/urban_legend;
- región;
- SEO;
- Article schema;
- backoffice/API;
- media.

No inferir ni completar automáticamente orígenes culturales dudosos.

## Oleada 4 — Integración editorial

PR #13 — feature/editorial-refresh-entities

Sin migraciones Laravel.

Dependencias:
- Oleadas 1, 2 y 3 validadas.

Acciones:
- merge/pull;
- sincronizar Apps Script desde fuente GitHub/clasp según flujo vigente;
- mantener MFA - Carga contenidos diario deshabilitada durante prueba manual.

Prueba controlada:
1. Artista CREAR draft/inactivo;
2. Artista ACTUALIZAR;
3. Receta CREAR draft/inactiva;
4. Receta ACTUALIZAR;
5. Mito CREAR draft/inactivo;
6. Mito ACTUALIZAR.

Validar en Sheet:
- ID_WEB;
- RESULTADO_API;
- ERROR_API vacío;
- FECHA_ENVIO_API;
- ENVIAR_API=N tras éxito.

Sólo después reactivar la tarea automática de carga.

## Oleada 5 — Discografía / Cancionero

PR #12 — feature/discography-modernization

Migración:
- 2026_09_01_000500_modernize_discography_and_songs.php

Antes:
- aprobar política operativa de derechos;
- no habilitar automatización de letras completas.

Después:

```bash
php artisan optimize:clear
php artisan view:cache
php artisan mfa:music:audit --active
```

Smoke:
- álbum create/update;
- tracklist;
- canción con letra histórica;
- canción sin letra;
- instrumental;
- credits;
- rights_status;
- frontend/SEO;
- alta rápida desde álbum sin placeholder.

Regla permanente:
- nunca generar letra para rellenar;
- una canción puede existir sin letra;
- no eliminar letras legacy masivamente;
- auditar derechos antes de enriquecimiento textual.

## Criterio de cierre global

El programa de modernización técnica puede considerarse desplegado cuando:
- las cinco oleadas estén verdes en producción;
- auditores reales hayan sido ejecutados;
- los flujos CREAR/ACTUALIZAR de la bandeja estén validados;
- la tarea de carga vuelva a estar habilitada;
- las colas P1 de cada entidad estén creadas;
- no existan errores 5xx atribuibles a los releases.

La recuperación editorial masiva continúa después, por score y por entidad; no bloquea el cierre técnico.
