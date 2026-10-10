## ADDED Requirements

### Requirement: ABM independiente de Salas y Lugares
El sistema SHALL ofrecer al administrador un ABM de Venue con nombre, dirección, localidad, provincia, coordenadas opcionales, estado, búsqueda y paginación. SHALL impedir borrar lugares utilizados y preservar slugs al editar.
#### Scenario: Lugar vinculado
- **WHEN** un administrador intenta borrar un lugar usado por eventos o conjuntos
- **THEN** se bloquea la baja y se ofrece inactivarlo
#### Scenario: Datos inválidos o duplicados
- **WHEN** se envían coordenadas fuera de rango o el mismo nombre y ubicación
- **THEN** se rechaza con errores por campo sin fusionar registros

### Requirement: Selección y propuesta editorial de lugares
Eventos y conjuntos SHALL permitir seleccionar lugares activos o proponer nuevos; solo un administrador SHALL activar propuestas. Dirección, localidad, provincia y coordenadas SHALL ser consistentes en formulario, búsqueda, ficha y JSON-LD.
#### Scenario: Propuesta de colaborador
- **WHEN** se carga un lugar nuevo desde una propuesta de evento
- **THEN** queda pendiente para revisión y el evento no se publica automáticamente
#### Scenario: Corrección del catálogo
- **WHEN** el administrador corrige la dirección de un lugar
- **THEN** se actualiza la ubicación de eventos vinculados sin cambiar su sede ni alterar históricos no vinculados
