## 1. Relevamiento (hecho)

- [x] 1.1 Estructura de `organizations`, `organization_members`, `social_accounts` y uso en Pasarela (`authorizeContent`).
- [x] 1.2 Policies de Interprete y Festival, y roles/permisos en base local (administrador 37, colaborador 17, prensa 21; 423 colaboradores).
- [x] 1.3 Confirmar que otra rama (`feature/event-programs-and-venues`) modifica Event*, AuthServiceProvider y routes.
- [x] 1.4 Revisados controladores Backend: Festival ya limita listado por `user_id`; Interprete no.

## 2. Persistencia (migración sujeta a confirmación)

- [x] 2.1 Migración reversible escrita (lint OK). **Sin ejecutar.**
- [x] 2.2 `migrate:status` mostrado; migración aplicada sola (autorizada) en la base local.

## 3. Dominio

- [x] 3.1 Modelo `OrganizationEntity` y relaciones en `Organization` (`interpretes()`, `festivales()`, `entities()`).
- [x] 3.2 Trait `HasOrganizations` en `Interprete` y `Festival` (relación y scope `managedBy`).
- [x] 3.3 `User::organizations()`, `User::canManage($entity)` con memoización por request.
- [x] 3.4 `OrganizationProvisioner::forEntity` idempotente.

## 4. Autorización

- [x] 4.1 `InterpretePolicy::update` y `FestivalPolicy::update` usando `canManage` y manteniendo el acceso legacy.
- [x] 4.2 Scope de listado en controladores Backend con flag `entity_management.scope_listings` (default `false`).
- [x] 4.3 Alta por no administrador: guardar `user_id` y aprovisionar organización.

## 5. Tests (`DatabaseTransactions`, nunca `RefreshDatabase`)

- [x] 5.1 Membresía activa editor/owner autoriza; member/pending/inactive no. (11 tests OK)
- [x] 5.2 Entidad no vinculada se deniega; varios administradores autorizados. (11 tests OK)
- [x] 5.3 Acceso legacy por `user_id`. (11 tests OK)
- [x] 5.4 Administrador conserva todo. (11 tests OK)
- [x] 5.5 Scope de listado con flag activo/inactivo y sin N+1. (11 tests OK)
- [x] 5.6 Provisioner idempotente. (11 tests OK)

## 6. Cierre

- [x] 6.1 Performance: 1 consulta indexada por usuario y tipo (memoizada); scope de listado verificado en 1 consulta.
- [x] 6.2 CHANGELOG y estado actualizados. Tag semver pendiente: se asigna al integrar en dev.
- [x] 6.3 Informado: implementado en local y validado (11 tests); regresión con 2 fallas previas ajenas. No integrado ni desplegado.
