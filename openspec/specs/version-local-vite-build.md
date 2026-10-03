# Cambio: versionar build Vite local

## Objetivo y aprobacion
Pedido y aprobacion explicita del usuario el 2026-09-09: compilar localmente antes del commit y distribuir public/build mediante push/pull.

## Alcance y archivos
- .gitignore: permitir public/build completo, mantener node_modules y public/hot excluidos.
- public/build: compilar y verificar manifiesto y assets.
- project/docs/releases/frontend-assets-deploy.md y project/docs/00_estado_actual.md: documentar el flujo.

## Criterio de aceptacion
Tras npm run build y npm run build:verify, Git debe detectar todos los assets generados. Codigo y build se incluyen en el mismo commit; produccion recibe la misma revision de main. Sin ejecucion de deploy, cambios de BD ni modificaciones funcionales. No agrega recursos ni costo de runtime; aumenta el historial Git.
