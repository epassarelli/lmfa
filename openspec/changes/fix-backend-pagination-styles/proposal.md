## Why

Los listados del backend administrativo quedaron visualmente rotos en `news` y otros CRUD porque el layout de AdminLTE comparte un bundle con `@tailwind base`, lo que contamina componentes bootstrap como la paginacion. El problema afecta navegacion, consistencia visual y productividad editorial en varias pantallas operativas.

## What Changes

- Aislar los estilos del backend para que AdminLTE no reciba resets globales de Tailwind pensados para frontend publico o formularios Jetstream.
- Estandarizar la paginacion de listados administrativos sobre una vista compatible con Bootstrap/AdminLTE.
- Validar el fix sobre varios listados backend ya paginados para evitar que `news` sea un arreglo aislado.

## Capabilities

### New Capabilities
- `backend-admin-listings`: Garantiza que los listados administrativos paginados rendericen controles de navegacion compatibles con AdminLTE sin quedar rotos por estilos globales.

### Modified Capabilities

## Impact

- Codigo afectado: `resources/views/layouts/adminlte.blade.php`, vistas de paginacion en `resources/views/vendor/pagination/`
- Vistas impactadas: listados backend como `news`, `events`, `festivales`, `interpretes`, `knowledge_articles`, `users` y otros CRUD paginados
- Dependencias/sistemas: Vite, Tailwind CSS, AdminLTE/Laravel pagination
