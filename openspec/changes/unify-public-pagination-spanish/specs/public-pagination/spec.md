## ADDED Requirements

### Requirement: Shared Spanish public pagination
Todas las vistas públicas con paginación MUST usar un componente Tailwind compartido, con textos visibles y accesibles en español incluso si el locale activo es inglés. El backend MUST conservar su paginación actual.

#### Scenario: Public listing contains multiple pages
- **WHEN** se muestra cualquier listado público con más de una página
- **THEN** se presenta navegación con Anterior, Siguiente y Página, estilos compartidos y región accesible denominada Paginación
- **AND** no aparecen controles Bootstrap ni textos de paginación en inglés

### Requirement: Responsive and accessible navigation
La navegación MUST mantener objetivos táctiles de al menos 44px, foco visible, identificación de página actual y extremos deshabilitados sin enlaces. MUST evitar desbordamiento horizontal en móvil y mantener numeración acotada en escritorio.

#### Scenario: Narrow viewport
- **WHEN** se muestra un listado a 320px de ancho
- **THEN** los controles permanecen dentro del contenedor y la numeración extendida se oculta

#### Scenario: First and last pages
- **WHEN** el usuario llega al primer o último resultado paginado
- **THEN** el control sin destino está deshabilitado y los enlaces válidos conservan rel prev o next

### Requirement: Preserve paginator semantics and filters
El componente MUST conservar filtros y parámetros de navegación, sin añadir consultas de conteo ni transformar paginadores simples en paginadores con totales.

#### Scenario: Simple pagination
- **WHEN** el listado usa simplePaginate
- **THEN** muestra página actual y navegación disponible sin total inventado

#### Scenario: Filtered numbered pagination
- **WHEN** se navega por un paginador numerado con filtros activos
- **THEN** los enlaces conservan filtros, nombre de parámetro de página y numeración con separadores cuando corresponde

#### Scenario: No additional pages
- **WHEN** el paginador está vacío o tiene una sola página
- **THEN** no se renderiza una región de paginación vacía
