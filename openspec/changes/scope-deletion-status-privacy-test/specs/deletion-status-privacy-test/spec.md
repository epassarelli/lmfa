## ADDED Requirements

### Requirement: Estado publico sin identificadores sensibles
La pagina publica de estado de eliminacion MUST mostrar solo informacion de
seguimiento y no MUST renderizar `external_user_hash` ni `user_id`.

#### Scenario: Solicitud completada
- **WHEN** una solicitud completada tiene hash externo e identificador interno
- **THEN** la respuesta publica incluye su codigo y estado, y excluye ambos
  identificadores sensibles
