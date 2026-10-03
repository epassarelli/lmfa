## Context

La acción `index()` de `RecetasController` arma dos colecciones cacheadas para la home pública de comidas. Hoy usa claves con interpolación de `$letra`, pero esa variable no existe en ese método. La corrección debe mantener el esquema actual de consultas, eager loading y límites.

## Goals

- Eliminar la referencia inválida que provoca el `500`.
- Mantener claves de caché estables y semánticas para la portada de comidas.
- Cubrir la ruta con un test feature para evitar regresiones.

## Non-Goals

- No cambiar SEO, markup ni copy editorial de la home.
- No cambiar la lógica de detalle o páginas alfabéticas.
- No introducir migraciones ni cambios de modelo.

## Approach

1. Reemplazar las claves de caché de `index()` por claves fijas de portada (`comidas:index:ultimas` y `comidas:index:visitadas`).
2. Agregar un test feature que inserte recetas publicadas, consulte la home y verifique `200 OK` y contenido básico esperado.
3. Ejecutar el test de recetas para confirmar que el fix no rompe cobertura existente.

## Risks

- Bajo riesgo funcional: el cambio solo afecta nombres de claves de caché en una ruta puntual.
- Riesgo residual menor: producción podría conservar entradas previas inútiles en caché hasta su expiración, pero las nuevas lecturas de home usarán claves válidas.
