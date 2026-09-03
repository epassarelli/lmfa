# Release gate — Peñas y Radios

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

Sólo queda apto para la decisión de merge cuando CI, preflight, seis operaciones controladas y ambos smoke sean verdes, sin pérdida legacy. El despliegue productivo debe comenzar oscuro; la activación pública de cada directorio es una decisión posterior e independiente.
