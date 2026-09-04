## Why

Los layouts contienen mensajes de depuración versionados que contaminan la consola del navegador y reducen la confianza operativa del backoffice. Deben retirarse antes de ampliar módulos, conservando los comportamientos JavaScript reales.

## What Changes

- Eliminar `console.log` de depuración y comentarios asociados en layouts activos.
- Añadir una prueba que evite reintroducir mensajes de depuración visibles en esos layouts.

## Capabilities

### New Capabilities
- `clean-browser-runtime`: layouts sin salida de depuración versionada en navegador.

### Modified Capabilities
- Ninguna.

## Impact

- Plantillas Blade de administración y selector de intérprete; prueba feature de acabado técnico.
