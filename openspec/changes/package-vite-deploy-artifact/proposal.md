## Why

El servidor productivo no dispone de Node/NPM y el deploy del 2026-09-08 omitió inicialmente `public/build`, dejando todo el sitio sin estilos. El pipeline ya compila Vite, pero no verifica la integridad del resultado ni entrega un artefacto descargable para el despliegue.

## What Changes

- Agregar una validación reproducible del manifiesto Vite y de cada archivo con hash que referencia.
- Ejecutar esa validación después del build en CI.
- Publicar `public/build` como artefacto descargable del workflow, sin incluir secretos ni dependencias de desarrollo.
- Documentar el procedimiento de build, descarga/subida y verificación para servidores sin Node.
- Incorporar pruebas automatizadas para builds completos, manifiestos inválidos y assets faltantes.

## Capabilities

### New Capabilities

- `vite-deploy-artifact`: garantiza que un bundle Vite íntegro pueda generarse fuera de producción y transferirse como unidad verificable al servidor.

### Modified Capabilities

Ninguna.

## Impact

- `.github/workflows/ci.yml`
- `package.json`
- validador y pruebas Node bajo `scripts/` y `tests/`
- documentación operativa y backlog en `project/docs/`
- no cambia APIs, base de datos, rutas ni runtime productivo
