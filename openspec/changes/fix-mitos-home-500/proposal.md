## Why

La home publica de `Mitos y leyendas` puede responder `500` en produccion por una referencia a una variable inexistente dentro del controlador frontend. El problema impacta una URL indexable, rompe navegacion publica y debe corregirse sin cambiar la arquitectura ni las URLs actuales.

## What Changes

- Corregir la logica de cache del `index()` de `MitosController` para que no dependa de una variable no definida.
- Asegurar que la home de `Mitos y leyendas` siga mostrando los bloques de mas visitados y ultimos sin errores.
- Corregir la referencia de ruta usada por el componente lateral de mitos para que apunte al nombre de ruta publico existente.
- Agregar validacion automatizada o una cobertura equivalente para evitar regresiones en la portada del modulo.

## Capabilities

### New Capabilities
- `mitos-frontend-stability`: Estabilidad y resolucion correcta de la home y enlaces publicos del modulo Mitos y leyendas.

### Modified Capabilities

## Impact

- Codigo afectado en `app/Http/Controllers/Frontend/MitosController.php`.
- Posible ajuste en `resources/views/components/sidebar/card-mitos.blade.php`.
- Cobertura en tests feature del frontend/SEO si no existe un test puntual del modulo.
- Sin cambios de base de datos, sin cambios de rutas publicas y sin impacto en APIs.
