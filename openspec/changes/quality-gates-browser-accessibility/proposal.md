## Why

La CI valida rutas y Blade, pero no protege de forma explícita la estructura accesible de las páginas críticas ni de respuestas públicas excesivas. Se necesita una base de calidad reproducible antes de incorporar E2E de navegador pesado.

## What Changes

- Añadir gates feature para landmarks, título, idioma y controles de navegación en páginas públicas críticas.
- Añadir presupuestos de tamaño de respuesta para las landings principales.
- Integrar esas pruebas en CI.

## Capabilities

### New Capabilities
- `public-quality-gates`: contratos automatizados de accesibilidad estructural y presupuesto de respuesta.

### Modified Capabilities
- Ninguna.

## Impact

- Pruebas feature, workflow CI y documentación de alcance. No añade credenciales ni cambia comportamiento público.
