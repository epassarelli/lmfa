# Release gate — Peñas y Radios

> Actualización 2026-09-08: el código y las migraciones ya fueron desplegados directamente en producción. Este documento conserva el gate exhaustivo como referencia, pero la activación práctica se considera reversible mediante flags y no exige repetir staging para mantener el release técnico.

## Estado productivo confirmado

- `main` desplegado y migraciones aplicadas.
- Home y `GET /healthz` responden `200`.
- El servidor no tiene NPM; el bundle Vite fue compilado fuera del servidor y `public/build` se subió completo después de una primera carga sin estilos.
- Los módulos permanecen protegidos por flags independientes.
- Falta confirmar que los flags sean efectivos y que Peñas/Radios aparezcan en NAV y respondan en sus rutas públicas.

## Activación mínima vigente

1. Definir `FEATURE_PENIA_DIRECTORY=true` y/o `FEATURE_RADIO_DIRECTORY=true` en `.env`.
2. Ejecutar `php artisan config:clear && php artisan config:cache`.
3. Confirmar con Tinker que `config('features.penia_directory')` y `config('features.radio_directory')` devuelvan `true`.
4. Ejecutar `php artisan responsecache:clear` y `php artisan view:clear` porque el NAV puede seguir servido desde la caché de página completa.
5. Verificar NAV, `/penias`, `/radios-de-folklore-argentino`, sitemaps y ausencia de errores en logs.
6. Ante una falla, apagar únicamente el flag afectado, regenerar config y limpiar response cache.

## Build sin Node en producción

El procedimiento canónico está en [Deploy de assets Vite sin Node](frontend-assets-deploy.md).

```bash
npm ci
npm run build
```

Estos comandos se ejecutan localmente o en CI. El deploy debe incluir juntos `public/build/manifest.json` y todos los archivos de `public/build/assets/`; subir solamente el manifiesto rompe CSS/JS por los hashes de Vite.

## Estado previo

- Trabajar desde `dev`; Eduardo decide y ejecuta la fusión a `main`.
- `FEATURE_PENIA_DIRECTORY=false` y `FEATURE_RADIO_DIRECTORY=false` durante migración, carga y validación inicial.
- No retirar `penias`, `radios` ni sus puentes legacy en este release.
- Conservar backup de base de datos y `.env` antes de migrar.

## Gate automatizado

El workflow CI debe completar:

- `migrate:fresh` sobre MySQL 8;
- sintaxis PHP y Apps Script;
- preflight core y directorios;
- compilación Blade y Vite;
- suites específicas de Peñas/Radio;
- suite PHPUnit completa;
- feature flags oscuros e independientes.

## Staging oscuro

### Evidencia local previa (2026-09-04)

- CI verde: commit `d655139`, run `33866188915`.
- Docker local: `http://mfa.localhost`, explícitamente separado de producción.
- Ambos flags efectivos en `false`.
- Smoke oscuro manual aprobado: home `200`; `/penias`, Radios, Programas y sitemaps de directorios `404`.
- El commit `2ed7c92` evita que `mfa.localhost` redirija al dominio de producción.

Esta sección describe el gate originalmente previsto. El despliegue productivo del 2026-09-08 lo reemplazó como condición previa; sus pasos quedan disponibles para regresiones y futuros releases de mayor riesgo.

1. Desplegar `dev` en un entorno separado de producción.
2. Mantener ambos flags en `false`.
3. Ejecutar migraciones y caches.
4. Ejecutar:

```bash
BASE_URL=https://URL-STAGING DIRECTORY_MODE=dark bash scripts/smoke-directories.sh
```

5. Confirmar que home responde `200` y las cinco superficies de directorios responden `404`.
6. Ejecutar auditorías iniciales y conservar CSV.

## Content Refresh controlado

1. Sincronizar Apps Script desde el commit aprobado de `dev` mediante **Apps Script Sync**.
2. Desactivar temporalmente el trigger diario.
3. Exportar `Contenidos` completa y ejecutar el preflight:

```bash
npm run apps-script:directory-pilot:check -- /ruta/Contenidos.csv
```

4. Ejecutar una por vez las seis filas: `CREAR/ACTUALIZAR` de Peña, Radio y ProgramaRadio.
5. Verificar en la hoja y backoffice que las altas estén `draft/pending` y los updates preserven campos omitidos.
6. Repetir auditores y contrastar los seis `ID_WEB`.

## Staging visible

1. Verificar y publicar únicamente los registros de prueba aprobados.
2. Habilitar ambos flags en staging y regenerar caches.
3. Ejecutar:

```bash
BASE_URL=https://URL-STAGING DIRECTORY_MODE=light \
PENIA_SLUG=slug-penia RADIO_SLUG=slug-radio PROGRAM_SLUG=slug-programa \
bash scripts/smoke-directories.sh
```

4. Revisar visualmente desktop/mobile, canonical, schemas, filtros, mapas, escucha, próxima emisión y sitemaps.
5. Volver los flags a `false` y confirmar nuevamente el smoke oscuro.

## Criterio para fusionar

Para futuros releases planificados, CI, preflight y smoke siguen siendo evidencia recomendable. Para el release ya desplegado, el cierre pendiente se limita a confirmar flags, NAV/rutas, contenido inicial y rollback por flag; el piloto Content Refresh es gate de automatización editorial, no del código ya instalado.
