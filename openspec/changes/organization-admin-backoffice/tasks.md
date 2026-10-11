## 1. Base
- [x] 1.1 Registro de tipos vinculables y tipos de organización en `config/entity_management.php`.
- [x] 1.2 Rutas `backend.organizations.*` (solo `administrador`) y entrada de menú.

## 2. Backend
- [x] 2.1 `OrganizationController` (index, create, store, edit, update) + `OrganizationRequest`.
- [x] 2.2 `OrganizationEntityController` (store, destroy, primary) y `OrganizationMemberController` (store, update, destroy).
- [x] 2.3 `OrganizationLookupController` (entidades y usuarios, máx. 20, mínimo 2 caracteres).

## 3. Vistas
- [x] 3.1 Listado con búsqueda, filtros y paginación.
- [x] 3.2 Formulario, panel de fichas vinculadas y panel de miembros con Select2 remoto.

## 4. Tests (`DatabaseTransactions`)
- [x] 4.1 Acceso solo administrador.
- [x] 4.2 Listado y filtros.
- [x] 4.3 Alta/edición, slug duplicado y desactivación que revoca permisos.
- [x] 4.4 Vínculos (alta, duplicado, tipo inválido, quitar).
- [x] 4.5 Miembros (alta, duplicado, aprobar, quitar) y permisos efectivos.
- [x] 4.6 Lookups.

## 5. Cierre
- [~] 5.1 CHANGELOG y estado actualizados; performance revisada. Falta validación visual en navegador por el usuario.
