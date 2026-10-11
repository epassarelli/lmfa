## ADDED Requirements

### Requirement: Editor enriquecido en Eventos
Alta y edición de eventos SHALL usar CKEditor local compartido, con párrafos H2/H3, conservar HTML existente y mostrar errores de validación sin perder contenido.
#### Scenario: Editar contenido HTML
- **WHEN** el administrador abre un evento con párrafos y listas
- **THEN** ve el editor enriquecido y puede guardar preservando estructura y texto

### Requirement: Reglas compartidas en ingresos editoriales
Backoffice, API, Bandeja Editorial y colaboraciones SHALL utilizar servicios comunes de eventos/conjuntos con validación de fechas, lugares, artistas y permisos, en transacciones. SHALL mantener datos existentes cuando un PATCH no incluye claves de relaciones.
#### Scenario: Aprobación editorial
- **WHEN** el administrador aprueba un evento propuesto con jornadas o artistas
- **THEN** se guardan vínculos mediante los servicios comunes y se publica solo el alcance revisado
#### Scenario: PATCH parcial
- **WHEN** se actualiza solo el título
- **THEN** se conservan relaciones y ubicación no incluidas
