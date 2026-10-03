- [x] 1. Habilitar relaciones de Festival fuera del piloto y eliminar cargas duplicadas.
- [x] 2. Paginar próximas fechas, preservar canonical y mostrar título/hora de jornada.
- [x] 3. Actualizar pruebas del piloto y agregar cobertura de visibilidad, vacíos, paginación y consultas.
- [x] 4. Ejecutar validación enfocada segura y registrar resultados en estado actual.

Validación local: 18 Feature pasan, 111 aserciones. Spec válida; diff sin errores. Sin integración ni despliegue. Checkout aislado tras un cambio concurrente de rama en el directorio principal.

Integración autorizada posterior: conflictos con dev resueltos, conservando ambas entradas de estado y la condición de hora `00:00` de dev. CHANGELOG Unreleased actualizado. Regresión combinada: 45 Feature pasan, 272 aserciones. Integrado a dev; sin push ni despliegue.

## Prueba visual con datos locales (autorizada por el usuario)

- [x] Cargar en la base local `mfa` cinco eventos futuros, dos noticias, un artista y un evergreen explícitamente ficticios, vinculados a Jesús María. Script revisable: `storage/app/local-demos/festival-links.php`, restringido a entorno local, transaccional e idempotente, sin modificar la ficha real ni desvincular relaciones existentes. Usuario y categoría demo propios; IDs registrados en manifest local. No producción, migraciones ni resets.
- [x] Invalidar response cache local y verificar por HTTP los cuatro módulos, paginación y destinos públicos.

Evidencia: HTTP 200 en ficha y segunda página; módulos upcoming_events, festival_artists, festival_context y festival_news visibles. Noches 1–3 en página 1, 4–5 en página 2. Destinos canónicos de evento, noticia, evergreen y artista HTTP 200, identificados como demo. No datos culturales reales inventados ni acciones en producción.
