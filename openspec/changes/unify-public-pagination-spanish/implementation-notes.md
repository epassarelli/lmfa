# Paginación pública unificada — 2026-09-08

Autorizada explícitamente por el usuario. Rama activa `main`; no se crearon ramas, commits ni despliegues. Se preservan los cambios de trabajo preexistentes.

## Inventario completo de controles existentes

| Entidad / lugar | Vista bajo `resources/views/frontend` | Paginador |
|---|---|---|
| Noticias | `noticias/index` | Simple |
| Artistas | `interpretes/index`, `interpretes/letra` | Simple |
| Canciones | `canciones/index`, `canciones/letra` | Simple |
| Discografías | `discos/index` | Simple |
| Festivales: índice, provincia, mes, provincia/mes | `festivales/index` compartida | Con total |
| Cartelera: índice y filtros territoriales/temporales | `shows/index` compartida | Con total |
| Recetas por letra | `recetas/letra` | Simple |
| Mitos por letra | `mitos/letra` | Simple |
| Enciclopedia por categoría | `knowledge/category` | Con total |
| Peñas | `penia-profiles/index` | Con total |
| Radios | `radios/index` | Con total |
| Programas | `radios/programs/index` | Con total |
| Clasificados públicos / Mis avisos | `classifieds/index`, `classifieds/mis-avisos` | Con total |
| Búsqueda legacy de clasificados | `classifieds/search` | Control sustituido preventivamente; sin ruta `classifieds.search` activa |

Total: 17 puntos reemplazados. Se retiraron 16 contenedores con márgenes particulares; el componente administra el espaciado y evita el doble borde de Discografías. También se tradujeron los rótulos ingleses de la vista legacy de búsqueda de clasificados; sus clases Bootstrap restantes son deuda legacy fuera del paginado, sin ruta activa.

Las fichas y miniportales que devuelven colecciones completas no tenían paginación visible. No se añadieron consultas ni paginación a esos listados. `NoticiasController::noticias()` tiene un paginador en un método no usado por la ruta pública del artista; la ruta activa usa `byArtista()` y una colección. Entrevistas/Videos conservan controladores legacy incompletos, sin vistas de paginación para reparar.

## Comportamiento

- Componente `public-pagination` y plantilla `pagination.public`, Tailwind explícito; el backend continúa con Bootstrap.
- Texto visible y accesible español aun con locale inglés; Anterior/Siguiente, página y resumen de resultados cuando hay total.
- Objetivos táctiles de 44px, foco visible, página actual identificada y extremos deshabilitados sin enlaces ficticios.
- En móvil se oculta la numeración; en tamaños intermedios ocupa una segunda fila y en escritorio amplio comparte fila con los controles.
- Query string, parámetro de página y fragmento preservados mediante Laravel. Sin cambios de canonical, rutas o conteos.

## Validación

- `PublicPaginationTest`: **7 tests, 41 assertions**, todos correctos. Pruebas de render reales con Blade, paginadores en memoria y sin acceso a BD.
- `npm run build`: correcto. CSS público 87,29 KB (13,52 KB gzip); no se añadió JavaScript. `npm run build:verify`: 13 assets declarados presentes.
- `openspec validate unify-public-pagination-spanish --strict`: correcto. `git diff --check`: correcto.
- El proveedor conserva `useBootstrapFive()`; los 17 puntos públicos usan explícitamente el nuevo componente. No quedan llamadas públicas directas a `links()`.
- Entorno: PHP 8.2.33 portable temporal descargado de windows.php.net con SHA-256 verificado. Se sincronizaron dependencias locales con `composer.lock`, sin scripts ni cambios al manifiesto/lock. La generación optimizada del autoload era lenta y se sustituyó por generación normal con un manifiesto temporal ya eliminado.
- Performance: sin consultas nuevas, sin cambio de `simplePaginate`, sin conteos extra y numeración acotada a la ventana nativa de Laravel. No se midieron TTFB ni Core Web Vitals.
- Navegador de la sesión sin conexiones disponibles y Docker apagado: **pendientes QA visual y recorrido de entidades con datos reales**. No se ejecutó despliegue.

### Checklist visual pendiente

Revisar los lugares inventariados con al menos dos páginas en anchos 320, 375, 768 y 1280px: ausencia de desbordamiento, separación uniforme, foco de teclado, estados primero/último, navegación y conservación de filtros. Confirmar que una sola página no deja borde ni espacio de controles vacío. Directorios sujetos a sus feature flags existentes.

El informe solicitado previamente está en `project/docs/releases/seo-fields-audit-2026-09-08.md`; las reparaciones SEO quedan fuera de esta implementación de paginado.
