## ADDED Requirements
### Requirement: Ejemplos locales identificables
La carga MUST crear un evento independiente y dos conjuntos de cuatro y cinco jornadas futuras, con intérpretes activos existentes en cada Event, dos sedes ficticias, y aviso DEMO LOCAL en títulos y cuerpos. MUST usar servicios de eventos, publicación local y al menos una excepción de precio por jornada. No modificar registros anteriores.
#### Scenario: Comprender la programación
- **WHEN** se ejecuta la carga autorizada en local/mfa
- **THEN** existen dos EventProgram, diez Event relacionados con artistas y dos Venue nuevos; el usuario puede navegar las fichas públicas y editar defaults/excepciones.
### Requirement: Seguridad e idempotencia
El script MUST rechazar otros entornos/bases, realizar inserciones transaccionales y registrar IDs/URLs. Una carga completa repetida MUST conservar los IDs; una parcial MUST detener sin sobrescribir.
#### Scenario: Repetición
- **WHEN** los tres ejemplos completos ya existen
- **THEN** se exportan sus IDs sin nuevas inserciones.
#### Scenario: Entorno no local
- **WHEN** APP_ENV o base difieren del entorno permitido
- **THEN** el script termina antes de escribir datos.
