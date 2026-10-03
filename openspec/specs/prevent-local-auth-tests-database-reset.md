# Evitar recreacion de la base local desde tests de autenticacion

## Alcance y autorizacion
Revision solicitada por el usuario el 2026-09-09 por entidades vacias. Correccion preventiva reversible requerida expresamente por AGENTS.md: reemplazar RefreshDatabase encontrado por DatabaseTransactions.

## Archivos
tests/Feature/AuthenticationTest.php, tests/Feature/PasswordResetTest.php y tests/Feature/RegistrationTest.php.

## Criterio de aceptacion
Los tres tests usan DatabaseTransactions; sintaxis PHP valida. No ejecutar tests, migraciones, restauraciones ni SQL modificatorio durante esta investigacion. No cambia el runtime publico ni sus consultas.
