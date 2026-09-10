# Deploy de assets Vite sin Node en producción

> Procedimiento vigente desde 2026-09-08. Producción no dispone de Node/NPM; nunca se compila el frontend en el servidor.

## Flujo vigente: build versionado (2026-09-09)

Por pedido explicito del usuario, `public/build` completo se incluye en Git.

1. Ejecutar `npm run build` y `npm run build:verify` en local antes del commit.
2. Incluir los cambios de codigo y todos los cambios de `public/build` (altas, modificaciones y bajas) en el mismo commit.
3. Hacer push e integrar esa revision en `main`, la rama de despliegue.
4. Hacer pull de `main` en el servidor: incluye manifiesto, CSS, JS y fuentes. No requiere NPM ni subida manual del build.

Recompilar tambien ante cambios de clases Tailwind en Blade. `node_modules` y `public/hot` permanecen excluidos. El artefacto CI queda como alternativa; no reemplazar el build desplegado por otro de una revision diferente.

## Alternativa: artefacto de CI

1. Confirmar que el workflow `CI` del commit a desplegar terminó verde.
2. Descargar el artefacto `vite-build-<SHA>` de esa corrida.
3. Verificar que contenga `manifest.json` y la carpeta `assets/`.
4. Conservar una copia del `public/build` productivo anterior.
5. Subir el contenido del artefacto como la carpeta completa `public/build`; no mezclar un manifiesto nuevo con assets anteriores.

El artefacto de CI se conserva durante 14 días y contiene solamente el bundle público, sin `.env`, `vendor` ni `node_modules`.

## Alternativa local

Desde el mismo commit que se desplegará:

```bash
npm ci --no-audit --no-fund
npm run build
npm run build:verify
```

Si la validación termina correctamente, transferir completa la carpeta `public/build`.

## Verificación en el servidor

No requiere NPM:

```bash
test -f public/build/manifest.json
test -d public/build/assets
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Confirmar después:

- home y una página interna responden `200`;
- CSS y JavaScript referenciados por el HTML responden `200`, no HTML ni `404`;
- `public/hot` no existe en producción;
- no aparecen errores Vite en `storage/logs/laravel.log`.

## Rollback

Si el frontend falla, restaurar como unidad la copia anterior de `public/build` y limpiar las cachés de Laravel. No restaurar únicamente `manifest.json`, porque sus hashes deben coincidir con los archivos de `assets/`.

La transferencia o el rollback productivo son operaciones humanas; este procedimiento y CI no realizan despliegues automáticamente.
