## ADDED Requirements

### Requirement: Backend listings must support query-based discovery controls
Los listados administrativos MUST exponer controles GET de descubrimiento para buscar y ordenar resultados sin perder el estado al paginar.

#### Scenario: Listing preserves search and sort state across pagination
- **GIVEN** un usuario aplica un texto de busqueda y un criterio de orden en un listado backend
- **WHEN** navega a otra pagina del paginator
- **THEN** los parametros activos permanecen en la URL
- **AND** el listado mantiene el mismo subconjunto y orden de resultados

### Requirement: Backend listings must search at least visible editorial fields
Cada listado backend MUST permitir buscar por texto sobre, como minimo, los campos visibles en tabla o sus equivalentes editoriales directos.

#### Scenario: News listing searches visible table fields
- **GIVEN** el listado de noticias muestra fecha, titulo, interprete principal, visitas y estado
- **WHEN** un editor busca una noticia por titulo o interprete visible
- **THEN** el listado devuelve coincidencias relevantes dentro del alcance autorizado del usuario

#### Scenario: Users listing searches visible identity fields
- **GIVEN** el listado de usuarios muestra nombre, email y roles
- **WHEN** un administrador busca por nombre o email
- **THEN** el listado devuelve coincidencias sin traer usuarios fuera del filtro aplicado

### Requirement: Backend listings must restrict ordering to allowed module criteria
Cada listado backend MUST aceptar solo criterios de orden definidos para su modulo, usando defaults estables cuando no se envie un criterio valido.

#### Scenario: Event listing orders by allowed date criterion
- **GIVEN** el listado de eventos permite ordenar por fecha del evento, titulo o estado editorial
- **WHEN** llega un `sort` permitido con direccion valida
- **THEN** la query aplica ese orden
- **AND** mantiene un desempate estable para evitar saltos entre paginas

#### Scenario: Invalid sort falls back to module default
- **GIVEN** un request envia un `sort` no permitido para el modulo
- **WHEN** el controlador construye la query
- **THEN** ignora ese valor
- **AND** usa el orden por defecto definido para el listado

### Requirement: Legacy full-collection listings must migrate before advanced discovery
Los listados administrativos que hoy cargan colecciones completas MUST migrar a una estrategia server-side/paginada antes de incorporar discovery consistente a escala.

#### Scenario: Album listing becomes paginated before adding full discovery controls
- **GIVEN** el listado de discos actualmente usa `get()` con relaciones y conteos
- **WHEN** se incorpora buscador y ordenamiento consistente
- **THEN** el listado primero se resuelve con paginacion server-side
- **AND** recien despues aplica buscador y orden sobre la query paginada
