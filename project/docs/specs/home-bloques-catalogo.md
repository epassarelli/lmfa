# Spec: Home con enlace a todas las noticias y cuatro bloques de catálogo

Estado: aprobada por el usuario en el chat (2026-11-11). Cambio reversible y de bajo riesgo; sin migraciones, rutas nuevas ni cambios de URLs.

## Alcance
1. Debajo de las noticias, botón «Ver todas las noticias» hacia `noticias.index`.
2. Reemplazar el bloque «Destacados del catálogo» (un intérprete, un disco, un festival y una receta) por cuatro bloques de hasta 4 ítems:
   - Próximos eventos: vigentes o en curso (`publiclyVisible` + `upcomingOrOngoing`), por `start_at` ascendente.
   - Últimos intérpretes: `estado = 1`, por `id` descendente (fecha de alta).
   - Festivales más visitados: `publishedVisible`, por `visitas` descendente.
   - Últimos discos: `estado = 1`, por `id` descendente.
3. Cada bloque enlaza a su sección (cartelera, intérpretes, festivales, discografías) y se omite si no tiene ítems.

## Fuera de alcance
Recetas en la Home, cambios en las tarjetas existentes, nuevos criterios editoriales.

## Reglas
- Reutiliza `x-show-card`, `x-biografia-card`, `x-festival-card` y `x-disco-card`.
- Las consultas usan eager loading y se cachean 10 minutos en `home:bloques`; la clave se invalida al guardar o borrar Evento, Intérprete, Festival, Álbum y demás modelos observados en `AppServiceProvider`.
- Los enlaces de sección son una adición de navegación y enlazado interno, no pedida literalmente (el usuario solo pidió el de noticias).

## Criterios de aceptación
- La Home muestra «Ver todas las noticias» con la URL de `noticias.index`.
- La Home no muestra «Destacados del catálogo».
- Cada bloque tiene como máximo 4 tarjetas y enlaza a su sección; si no hay datos no se renderiza.

## Impacto
- Performance: 4 consultas acotadas (LIMIT 4) con eager loading, cacheadas; 16 tarjetas más con imágenes lazy. Se elimina la consulta de destacados. No medido en producción.
- SEO: más enlaces internos a las secciones; H1, metadescripción y URLs sin cambios.

## Validación
`tests/Feature/HomeBlocksTest.php` (3 tests) y render real local con los cuatro bloques.
