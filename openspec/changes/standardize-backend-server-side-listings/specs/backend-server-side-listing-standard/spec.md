## ADDED Requirements

### Requirement: New backend listings must use a unified server-side discovery contract
Todo listado administrativo nuevo o refactorizado MUST usar un contrato server-side comun para discovery y navegacion.

#### Scenario: A new backend ABM defines its listing contract
- **GIVEN** se crea o refactoriza un ABM con listado administrativo
- **WHEN** se define su indice
- **THEN** el listado expone como base `search`, `sort`, `direction` y paginacion server-side
- **AND** cualquier filtro adicional del modulo se integra sobre ese mismo contrato

### Requirement: Backend listings must restrict sorting to explicit module whitelists
Todo listado administrativo MUST restringir el ordenamiento a criterios permitidos por modulo.

#### Scenario: Sort key is validated against module definition
- **GIVEN** un request envia un valor `sort`
- **WHEN** el backend construye la query del listado
- **THEN** solo acepta claves definidas para ese modulo
- **AND** si el valor no es valido usa el orden por defecto del listado

### Requirement: Backend listings must support clickable sortable headers without client-side full-table sorting
Los listados administrativos MUST ofrecer encabezados clickeables para ordenar preservando un modelo server-side.

#### Scenario: User clicks a sortable header
- **GIVEN** una columna esta marcada como ordenable
- **WHEN** el usuario activa ese encabezado
- **THEN** el listado actualiza `sort` y `direction` en la URL
- **AND** la tabla se re-renderiza desde servidor con el nuevo orden

### Requirement: Backend listing definitions must separate query mechanics from module rules
La definicion de cada listado MUST separar la mecanica transversal de listado de las reglas especificas del modulo.

#### Scenario: A module declares its listing behavior
- **GIVEN** un modulo administrativo define su indice
- **WHEN** se implementa el listado
- **THEN** el modulo declara columnas visibles, buscables, ordenables, filtros y orden por defecto
- **AND** la aplicacion de esos criterios reutiliza una capa comun

### Requirement: Legacy collection-based listings must migrate to paginated server-side queries before advanced discovery
Los listados legacy que hoy traen colecciones completas MUST migrar primero a queries paginadas server-side.

#### Scenario: Legacy listing is modernized
- **GIVEN** un listado usa `get()` o una grilla client-side heterogenea como base
- **WHEN** se decide modernizarlo
- **THEN** primero se lo lleva a paginacion server-side estable
- **AND** luego se incorporan buscador, orden y filtros bajo el estandar comun
