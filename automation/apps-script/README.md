# MFA — Google Apps Script

Este directorio versiona el Apps Script vinculado a **Mi Folklore Argentino — Bandeja editorial MVP**.

## Fuente de verdad

- Código: `automation/apps-script/Código.js`.
- El proyecto remoto se vincula mediante `.clasp.json` local.
- `.clasp.json` y `.clasprc.json` no se versionan.
- El manifiesto real `appsscript.json` se obtiene del proyecto remoto durante el bootstrap; no se inventa ni sobrescribe a ciegas.

## Primera vinculación — una sola vez

1. En Apps Script → **Configuración del proyecto** → copiar **Script ID / ID de secuencia de comandos**.
2. Desde la raíz del repo:
   `npm run apps-script:link -- <SCRIPT_ID>`
3. Autorizar clasp:
   `npx --yes @google/clasp login`
4. Importar manifiesto/archivos remotos preservando nuestro Código.js:
   `npm run apps-script:bootstrap`
5. Revisar:
   `git status`
   `npm run apps-script:status`

No ejecutar `apps-script:push` antes de comprobar que el proyecto vinculado es el correcto.

## Uso habitual

- Estado: `npm run apps-script:status`
- Traer remoto: `npm run apps-script:pull`
- Enviar Git → Apps Script: `npm run apps-script:push`

Después del primer push controlado podemos agregar una GitHub Action manual con credenciales en Secrets, eliminando la necesidad de entrar al editor para cambios normales.

## Content Refresh

- Piloto Artistas/Recetas/Mitos: `npm run apps-script:pilot:check -- /ruta/Contenidos.csv`.
- Piloto Peñas/Radios/Programas: `npm run apps-script:directory-pilot:check -- /ruta/Contenidos.csv`.
- Runbooks: `CONTENT_REFRESH_PILOT.md` y `DIRECTORY_REFRESH_PILOT.md`.

Ambos preflight son offline: no usan token ni realizan escrituras. Las altas de Peñas, Radios y Programas se envían siempre como borradores pendientes y requieren revisión humana en el backoffice.
