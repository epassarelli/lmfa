## Context

La auditoría encontró logs de prueba en el layout AdminLTE y logs de inspección en el selector de intérprete. No son telemetría ni manejo de errores.

## Goals / Non-Goals

**Goals:** eliminar sólo salida de depuración y proteger el resultado con prueba.

**Non-Goals:** no reescribir JavaScript, eventos Livewire ni introducir un SDK de observabilidad.

## Decisions

- Se eliminan las sentencias y comentarios de depuración, preservando selectores, listeners y flujos.
- La regresión se detecta leyendo las plantillas afectadas desde una prueba feature.

## Risks / Trade-offs

- [Eliminar una línea útil] → se limita a mensajes literales de prueba/inspección y se compila Blade después.
