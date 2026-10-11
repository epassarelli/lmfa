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
