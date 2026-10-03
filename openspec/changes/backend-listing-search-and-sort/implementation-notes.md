## Listing Inventory

### First wave implemented on existing server-side pagination
- `backend.news.index`
- `backend.events.index`
- `backend.festivales.index`
- `backend.interpretes.index`
- `users.index`
- `backend.newsletter.index`
- `pasarela.templates.index`
- `pasarela.publication-requests.index`
- `pasarela.notifications.index`

### Legacy listings migrated to the unified server-side pattern
- `backend.albunes.index` / `backend.discos.index`
- `backend.classifieds.index`
- `backend.comidas.index`
- `backend.contributions.admin.index`
- `backend.canciones.index` / `backend.canciones.get`
- `backend.mitos.index`

### Legacy listings still pending server-side migration
- Ninguno en esta ola de listados backend/pasarela relevados por la spec.

## Performance Notes

- The first wave keeps `withQueryString()` and whitelisted `sort` values to avoid arbitrary ordering.
- Searches on related labels (`interpretes`, `categoria`, `provincia`, `locality`, `mes`, `roles`) use `whereHas`; if these lists grow materially, the next step should be measuring query plans and adding indexes only where justified.
- `classifieds` ahora resuelve los contadores por estado con un agregado agrupado y renderiza una sola tabla paginada en lugar de cargar tres colecciones completas en memoria.
- `contributions` resuelve la etiqueta visible del payload con extraccion JSON en SQL para la busqueda; si esta tabla crece mucho, el siguiente paso deberia ser promover ese campo a una columna indexable o una proyeccion normalizada.
- `backend.canciones.index` conserva el modelo asincronico con DataTables, pero ahora con filtro por estado, orden controlado por columnas visibles y persistencia del estado en la URL para converger con el criterio general sin perder la UX de cabeceras clickeables.
