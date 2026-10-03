## Context

El backend usa `AdminLTE` pero hoy carga `resources/css/app.css`, que incluye `@tailwind base` junto con estilos pensados para otras superficies. Ese reset global puede alterar listas, SVG y elementos inline-flex usados por las vistas de paginacion de Laravel, generando controles rotos en varios indices administrativos.

## Goals

- Evitar que el backend administrativo herede resets globales de Tailwind innecesarios.
- Forzar una salida de paginacion consistente con Bootstrap/AdminLTE en los listados del panel.
- Mantener sin cambios el frontend publico y las pantallas guest que ya dependen del bundle actual.

## Non-Goals

- No rediseñar los listados administrativos.
- No cambiar queries, ordenamientos ni logica de paginacion server-side.
- No modificar el bundle publico `app-public.css`.

## Approach

1. Crear un stylesheet especifico para backend que importe Font Awesome y solo las capas/utilidades realmente necesarias, evitando `@tailwind base`.
2. Cambiar el layout `resources/views/layouts/adminlte.blade.php` para cargar ese bundle del backend en lugar de `resources/css/app.css`.
3. Configurar `Paginator::useBootstrapFive()` en `AppServiceProvider` para que `{{ $paginator->links() }}` use markup compatible con AdminLTE de forma uniforme.
4. Validar visualmente y por inspeccion de vistas paginadas que indices como `news`, `events`, `festivales`, `interpretes` y `knowledge_articles` sigan renderizando controles correctos sin tocar cada archivo.

## Risks

- Riesgo bajo: alguna pantalla admin podria depender incidentalmente de un reset de Tailwind; si aparece, conviene resolverlo de forma localizada y no reintroduciendo `@tailwind base`.
- Riesgo residual menor: guest/auth sigue usando `app.css`, por lo que el aislamiento se limita al backend y no cambia otras superficies existentes.
