## Why

Las homes y listados publicos principales del portal siguen entregando demasiados registros y bloques secundarios para mobile. Eso aumenta el tiempo de respuesta del servidor, el HTML inicial, el trabajo de render y la cantidad de imagenes/relaciones resueltas aunque el usuario no necesite ver tanto contenido de entrada.

## What Changes

- Reducir la cantidad de registros iniciales en home y listados publicos pesados para privilegiar carga rapida y descubrimiento progresivo.
- Acotar bloques editoriales secundarios y sidebars cacheadas en secciones de alto trafico.
- Documentar el criterio para futuros desarrollos publicos: mobile-first, menos contenido inicial, cache por bloque y paginacion contenida.

## Capabilities

### New Capabilities
- `public-mobile-performance-budget`: define limites iniciales de carga para homes y listados publicos de alto trafico.

### Modified Capabilities
- `public-home-and-index-delivery`: actualiza la entrega server-side de noticias, discos, letras, artistas, festivales, comidas y mitos para priorizar mobile.

## Impact

- Controladores frontend publicos: home, noticias, discos, canciones, interpretes, festivales, recetas y mitos
- Documentacion OpenSpec del criterio de performance mobile
- Estado operativo del proyecto en `project/docs/00_estado_actual.md`
