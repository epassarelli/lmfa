# Spec: menús — backoffice y NAV del front

Estado: aprobada por el usuario en el chat (2026-11-11). Cambio visual reversible; sin migraciones, rutas ni URLs nuevas.

## Alcance
1. Backoffice (`config/adminlte.php`): intercambiar posiciones. «Centro de Moderación» pasa al primer lugar del menú; «Publicaciones en redes» pasa a la sección «Astronomía y audiencia», después de «Avisos Clasificados». Se conservan permisos (`can`), ícono y el contador de pendientes (`pending`). El orden alfabético por sección que exige `BackofficeSidebarTest` se mantiene (corrige el fallo previo de ese test).
2. NAV público (`components/navbar.blade.php`): «Discos» y «Canciones» pasan a enlaces principales, a continuación de «Festivales», y se quitan del desplegable «Más» (que conserva Clasificados y Contacto).

## Validación
`tests/Feature/NavMenusTest.php` (2 tests), `BackofficeSidebarTest` y las pruebas de navegación/Home. Además se actualiza `PublicMobilePerformanceBudgetTest`, que fijaba 6 noticias en la Home (límite anterior a la decisión de 24).

## Impacto
- UX: menos clics a Discos y Canciones. En pantallas angostas el menú principal es más largo (el menú móvil ya es una lista).
- SEO/performance: enlaces internos a Discos y Canciones visibles en todas las páginas; sin costo medible.
