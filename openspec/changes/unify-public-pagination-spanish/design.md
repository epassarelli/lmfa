## Context

`AppServiceProvider` activa `Paginator::useBootstrapFive()` para el backend. Hay 17 llamadas públicas a `links()` que heredan ese formato. El frontend activo carga Tailwind. Existen paginadores simples y con totales; Festivales reutiliza su índice para provincia, mes y combinación.

## Goals / Non-Goals

**Goals:** todos los controles existentes con apariencia compartida, español, navegación real por enlaces y adaptación a móvil.

**Non-Goals:** cambiar backend, consultas, tamaños de página, rutas SEO o completar módulos legacy sin modelo/vista funcional.

## Decisions

- Usar `<x-public-pagination>` explícito en frontend. Evita que una configuración global para AdminLTE vuelva a contaminar las páginas públicas.
- Una plantilla `pagination.public` admite `Paginator` y `LengthAwarePaginator`. Numeración compacta en escritorio para este último, anterior/siguiente y página actual en móvil. No se inventan totales para `simplePaginate`.
- Usar colores naranja y gris del portal, controles de al menos 44px, foco visible, `aria-current`, estados deshabilitados y etiquetas españolas literales. Sin SVG de tamaño indeterminado ni JavaScript.
- Preservar query string con el mecanismo nativo del paginador y ventana de una página a cada lado. No se modifican las consultas ni los canonical.
- Pruebas de render con paginadores en memoria (sin BD), build Vite y revisión visual si el entorno lo permite.

## Risks / Trade-offs

- Assets públicos requieren recompilación para las clases Tailwind nuevas → verificar build; despliegue fuera de alcance.
- Docker local no disponible y PHP fuera del PATH → buscar runtime local existente; dejar explícito cualquier límite de validación.
- Hay código legacy de entrevistas/videos incompleto → inventariar, sin crear páginas nuevas en este cambio estético.
