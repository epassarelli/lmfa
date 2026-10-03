# Diseño

La ficha Festival deja de depender de flag y allowlist. Esta decisión reemplaza el requisito de gate de `festival-vivo-journey` para la superficie Festival. Al integrar con dev, la ficha Evento ya muestra relaciones públicas por `event-detail-navigation-and-relations`; se preserva ese comportamiento. Artista y las continuaciones del servicio original conservan el piloto.

`forFestival` consulta una sola vez cada colección: próximos eventos activos/publicados desde el comienzo del día, artistas activos (6), evergreen visible (4), noticias públicas (3). Eventos: orden estable `start_at`, `events.id`, paginación de 3 mediante `eventos_page`. La vista conserva canonical de ficha y usa `noindex,follow` cuando se solicita una página posterior. Navegación HTML con ancla a próximas fechas, sin JS adicional.

Se eliminan las cargas completas del controlador y se asignan las colecciones acotadas al modelo para preservar el fallback de imagen relacionado. Las tarjetas de eventos del bloque usan el título del evento y la hora, sin alterar las demás tarjetas. La resolución del conflicto conserva la condición de dev que oculta `00:00` cuando representa una hora sin definir.

Pruebas transaccionales en la BD local persistente, sin DDL, seeders externos ni procesos externos. Response cache desactivado en las nuevas pruebas para evitar escritura de archivos de caché y verificar rendering real. Sin migraciones ni limpieza de cachés durante esta implementación; al desplegar será necesario invalidar response cache mediante el flujo habitual.
