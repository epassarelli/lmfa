## Why

Los paginados públicos heredan Bootstrap del proveedor global aunque el frontend usa Tailwind. El usuario solicita corregir todos los controles, usar solo español y mantener continuidad estética entre entidades.

## What Changes

- Componente público compartido con estilos Tailwind, navegación accesible y textos españoles independientes del locale.
- Aplicación a cada lugar de paginación pública, incluyendo Mis avisos y vistas auxiliares existentes.
- Preservación de filtros, URLs y paginadores simples sin consultas adicionales.
- Inventario por entidad, pruebas de render y revisión responsive.
- Autorización: pedido explícito del usuario del 2026-09-08; alcance reversible de presentación, sin despliegue ni cambios de datos.

## Capabilities

### New Capabilities
- `public-pagination`: presentación consistente, responsive y en español de paginadores públicos.

### Modified Capabilities

Ninguna.

## Impact

Vistas `resources/views/frontend/**` con paginación, nuevo componente `resources/views/components/public-pagination.blade.php`, plantilla `resources/views/pagination/public.blade.php`, pruebas Feature sin BD y documentación de estado. Se conserva la configuración Bootstrap del backend. No se alteran consultas, rutas, dependencias ni esquema de datos.
