## Objetivo

Bajar el costo inicial de renderizado y transferencia de las superficies publicas mas pesadas sin cambiar URLs, SEO tecnico ni arquitectura general.

## Estrategia

1. Reducir contenido above-the-fold y primeras tandas de cards.
2. Limitar bloques auxiliares que duplican contenido en una misma respuesta.
3. Mantener cache por bloque cuando el contenido siga existiendo, pero con colecciones mas chicas.
4. Preservar la navegacion por paginacion, filtros existentes y rutas canonicas.

## Decisiones

- La home publica mostrara menos noticias iniciales porque hoy el descubrimiento puede continuar desde la seccion de noticias.
- Los listados alfabeticos o catalogos grandes reduciran su paginacion inicial para mobile.
- Festivales conservara su home navegable, pero con menos destacados y menos piezas relacionadas simultaneas.
- Comidas y mitos mantendran sus bloques legacy mientras se homogeniza el frontend, pero con colecciones secundarias mas acotadas.

## Fuera de alcance

- Rediseño visual de las secciones publicas
- Nuevos buscadores publicos
- Reestructuracion de componentes Blade
- Cambios sobre Enciclopedia
