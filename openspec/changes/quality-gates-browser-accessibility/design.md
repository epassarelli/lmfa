## Context

El stack Blade permite validar HTML renderizado en PHPUnit. Playwright no está instalado y añadirlo requiere navegadores y mantenimiento adicional.

## Goals / Non-Goals

**Goals:** gates rápidos para estructura accesible y tamaño de HTML de rutas críticas.

**Non-Goals:** no sustituir auditoría manual con lector de pantalla ni Lighthouse real en staging.

## Decisions

- Usar tests feature de HTML renderizado, ya soportados por CI.
- Cubrir home, Noticias, Festivales, Peñas y Radios cuando sus flags estén habilitados en tests.
- Mantener presupuestos sobre HTML, no sobre LCP/JS transferido, que requiere navegador y entorno real.

## Risks / Trade-offs

- [Cobertura parcial de accesibilidad] → documentar que es un gate estructural y dejar Lighthouse/staging como evolución posterior.
