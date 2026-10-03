## Why

La home pública de la sección de comidas puede responder `500` en producción por una referencia a una variable inexistente dentro de las claves de caché del controlador. El error impacta una ruta indexable y rompe la navegación principal de un módulo público ya operativo.

## What Changes

- Corregir la generación de claves de caché en la home pública de comidas para que no dependan de una variable inexistente.
- Agregar cobertura feature para la ruta `GET /recetas-de-comidas-tipicas-argentinas`.
- Verificar que la home siga mostrando bloques de recetas publicadas sin degradar el comportamiento actual de detalle y páginas por letra.

## Capabilities

### New Capabilities
- `public-recipes-home-stability`: Garantiza que la portada pública de recetas responda correctamente y conserve su comportamiento editorial básico.

### Modified Capabilities

## Impact

- Código afectado: `app/Http/Controllers/Frontend/RecetasController.php`
- Tests afectados: `tests/Feature/Recipes/PublicRecipesFrontendTest.php`
- Rutas afectadas: `comidas.index`
