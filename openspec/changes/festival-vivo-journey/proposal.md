## Why

El portal ya posee Festivales, Eventos y Artistas publicados, pero sus relaciones no forman un recorrido público uniforme ni medible. Este cambio convierte esas relaciones persistidas en un primer recorrido de descubrimiento sin crear otra entidad ni alterar URLs existentes.

## What Changes

- Agregar el recorrido `Festival -> Evento futuro -> Artista` y sus continuidades editoriales en Festival, Evento y Artista.
- Reutilizar exclusivamente relaciones persistidas y scopes públicos canónicos.
- Incorporar un feature flag y allowlist de Festivales para un piloto cerrado y reversible.
- Añadir componentes server-side, SEO seguro e instrumentación de analítica sin datos personales.
- Corregir las consultas relacionadas que hoy pueden cargar contenido inactivo, no limitar en SQL o construir URLs según texto visible.

## Capabilities

### New Capabilities

- `festival-vivo-journey`: Recorrido transversal y medible entre Festival, Evento y Artista bajo un piloto gobernado por flag y allowlist.

### Modified Capabilities

- Ninguna.

## Impact

- Afecta controladores, vistas, componentes, JSON-LD, configuración, JavaScript de analítica y pruebas de Festival, Evento y Artista.
- No requiere migraciones, cambios de datos ni URLs nuevas.
- La activación pública depende de una auditoría read-only que demuestre al menos dos Festivales elegibles.
