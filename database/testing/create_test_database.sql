-- Base aislada para tests (mfa_test). SOLO crea una base nueva y vacia; no toca `mfa`.
-- Ejecutar una vez, como root del contenedor (sin escribir la clave):
--   docker exec -i lmfa-db-1 sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD"' < database/testing/create_test_database.sql
--
-- Despues, con .env.testing apuntando a mfa_test:
--   docker compose exec --user www-data app php artisan migrate:status --env=testing   (debe listar todo Pending)
--   docker compose exec --user www-data app php artisan migrate --env=testing          (solo tras revisar el status)
-- Los tests asumen datos de referencia (p. ej. provincias): ver "Datos de referencia" en
-- openspec/changes/isolated-test-database/spec.md.

CREATE DATABASE IF NOT EXISTS `mfa_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `mfa_test`.* TO 'mfa'@'%';
FLUSH PRIVILEGES;
