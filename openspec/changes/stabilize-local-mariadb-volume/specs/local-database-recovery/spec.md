## ADDED Requirements

### Requirement: Volumen local administrado por Docker
El servicio de base de datos de desarrollo SHALL usar un volumen nombrado
administrado por Docker en lugar de montar directamente el directorio de datos
de Windows.

#### Scenario: Inicio de la base reconstruida
- **WHEN** el operador inicia el servicio `db` con la configuracion nueva
- **THEN** MariaDB MUST crear o usar el volumen nombrado sin modificar
  `database_local`

### Requirement: Recuperacion reversible
La configuracion y documentacion SHALL permitir volver al directorio anterior
sin eliminar el volumen nuevo ni el respaldo local.

#### Scenario: Reversion por fallo de inicializacion
- **WHEN** la validacion de conectividad o migraciones falla en el volumen nuevo
- **THEN** el operador MUST poder restaurar el mount anterior sin ejecutar
  comandos destructivos

### Requirement: Validacion previa a uso de datos
El entorno reconstruido SHALL requerir comprobacion de conectividad e historial
de migraciones antes de cargar datos demo o ejecutar pruebas que escriban en la
base.

#### Scenario: Base nueva disponible
- **WHEN** MariaDB inicia sobre el volumen nombrado
- **THEN** el procedimiento MUST verificar `mysqladmin ping` y
  `php artisan migrate:status` antes de continuar
