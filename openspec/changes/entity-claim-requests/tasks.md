## 1. Persistencia (migración sujeta a confirmación)
- [x] 1.1 Migración reversible `entity_claim_requests`.
- [x] 1.2 `migrate:status` mostrado y confirmación humana recibida; migración aplicada sola en la base local.

## 2. Dominio
- [x] 2.1 Modelo `EntityClaimRequest` y relaciones.
- [x] 2.2 `EntityClaimService` (solicitar, cancelar, aprobar, rechazar) con límites.
- [x] 2.3 Configuración de relaciones y límite de pendientes.

## 3. Backend
- [x] 3.1 `MyEntitiesController` (Mis fichas, solicitar, cancelar, búsqueda) para cualquier autenticado.
- [x] 3.2 `EntityClaimRequestController` (listado, detalle, aprobar, rechazar) solo administrador.
- [x] 3.3 Rutas y entradas de menú.

## 4. Vistas
- [x] 4.1 Mis fichas con formulario y estados.
- [x] 4.2 Listado y detalle de solicitudes para el administrador.

## 5. Demo y tests
- [x] 5.1 Seeder demo con solicitudes pendientes.
- [x] 5.2 Tests (usuario sin rol, validaciones y límites, cancelar, aprobar con y sin organización, rechazar, doble revisión, organización inactiva, permisos).

## 6. Cierre
- [x] 6.1 CHANGELOG, estado actual y revisión de performance.
