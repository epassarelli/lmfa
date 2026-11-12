# Spec: Listado de Discos del backoffice — filtro por intérprete y portada contenida

- **ID:** `discos-listado-filtro-interprete`
- **Rama:** `feature/spotify-artistas-discos`
- **Autonomía:** `IA_AUTONOMA` (reversible, sin cambios de esquema). Pedido explícito del usuario el 2026-11-12.

## Contexto y problema
Al revisar los discos propuestos desde Spotify, el listado de `backoffice/discos` no permite ver los de un solo artista, y la portada se muestra a tamaño natural (el componente `x-optimized-image` ignora el atributo `style`), lo que vuelve gigante cada fila.

## Alcance
1. Filtro **Intérprete** (Select2, con opción «Todos») dentro de la barra de filtros existente, vía `?interprete_id=`. Se combina con búsqueda, orden y paginación (`withQueryString`).
2. Portada del listado en una caja fija de 50×50 px con `overflow:hidden` y la imagen cubriendo la caja (también en las imágenes legacy y la de «sin imagen»).

### Fuera de alcance
Filtro por estado, columnas nuevas, cambios en otros listados y en el componente `x-optimized-image`.

## Reglas
- Quien está acotado (`isScopedToManagedInterpretes` / colaborador) solo ve en el selector los artistas de los discos que ya ve en el listado (propios o de artistas que administra).
- Un `interprete_id` no numérico o inexistente se ignora o devuelve listado vacío; nunca error.

## Criterios de aceptación
- Dado un `interprete_id`, cuando se abre el listado, entonces solo aparecen discos de ese artista y el selector queda marcado.
- Dado el listado, entonces cada portada está dentro de un contenedor de 50×50 px.

## Impacto
Performance: una consulta extra liviana (`id`, `interprete`) para el selector. SEO: ninguno (backoffice). Validación: test Feature `AlbumListFilterTest`.
