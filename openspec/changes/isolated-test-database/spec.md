# Cambio: isolated-test-database

## Objetivo
Que `php artisan test` local corra contra una base propia (`mfa_test`) y no contra `mfa` (datos reales).

## Problema actual
`.env.testing` apunta a `mfa`. 13 tests de la suite fallan por colision con datos reales (slugs de knowledge_categories, conteos de auditoria, grupos del torneo). `DatabaseTransactions` no protege operaciones de esquema ni procesos externos.

## Alcance incluido
- `.env.testing`: `DB_DATABASE=mfa_test`.
- `database/testing/create_test_database.sql` (revisable; lo ejecuta el usuario).
- CI sin cambios: el job fija `DB_DATABASE: mfa` por variable de entorno, que prevalece sobre `.env.testing`.

## Fuera de alcance
- Crear la base, migrar o copiar datos (los ejecuta el usuario / requiere su confirmacion).
- Cambiar tests o seeders.

## Datos de referencia (a resolver tras la primera corrida)
`provincias` no se siembra en migraciones y varios tests usan `Provincia::firstOrFail()`; `ci.yml` tampoco siembra. Tras migrar `mfa_test`, correr la suite, listar los tests que fallan por tablas vacias y decidir con el usuario: seeder de testing versionado (preferido, reproducible en CI) vs copia puntual desde `mfa` (solo lectura sobre `mfa`).

## Validacion
Suite completa en `mfa_test`; `mfa` sin cambios de conteos. Los 13 fallos actuales deben desaparecer o quedar explicados.

## Riesgos
Hasta que exista `mfa_test`, los tests locales fallan con "Unknown database" (falla segura, no toca datos reales). Reversible: volver `DB_DATABASE=mfa`.

## Aprobacion
Pedido del usuario en el chat (2026-10-11): "haz lo tuyo y sigamos en el orden que dices".

## Resultado (2026-10-12, ejecutado a pedido del usuario)
- `mfa_test` creada con `database/testing/create_test_database.sql`, migrada (87 migraciones, igual que `mfa`) y sembrada con `TestingReferenceDataSeeder` (39 permisos y roles administrador/colaborador/verificado, igual que la base real). `mfa` no se tocó.
- Antes de sembrar, la suite en base limpia mostro 9 fallos por datos de referencia faltantes (roles sin permisos, `user_id` fijo en 1, `province_id` fijo en 1 en `PeniaProfileFactory`); se corrigieron.
- Resultado: **557 pasan, 0 fallan, 2 omitidos** en `mfa_test`; contra `mfa` quedan los 13 previos por datos reales (546 pasan).
- CI: se agrega el paso `db:seed --class=TestingReferenceDataSeeder` luego de `migrate:fresh`.
- `RolesAndPermissionsSeeder` (historico) esta desfasado (crea el rol `prensa`); no se usa para tests.
