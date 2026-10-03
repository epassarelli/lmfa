## ADDED Requirements

### Requirement: Backend paginated listings must render AdminLTE-compatible pagination
Los listados paginados del backend MUST renderizar controles de paginacion compatibles con Bootstrap/AdminLTE para preservar navegacion, iconografia y espaciados del panel administrativo.

#### Scenario: Backend listing renders Bootstrap pagination markup
- **GIVEN** un listado administrativo usa `{{ $paginator->links() }}`
- **WHEN** Laravel renderiza la paginacion del backend
- **THEN** la salida usa una vista Bootstrap compatible con AdminLTE
- **AND** los enlaces anterior, siguiente y paginas numeradas conservan estructura y clases esperadas por Bootstrap

### Requirement: Backend layout must avoid frontend Tailwind base resets
El layout administrativo MUST evitar resets globales de Tailwind pensados para frontend publico o guest cuando esos resets rompan componentes nativos de AdminLTE.

#### Scenario: Backend layout loads isolated admin stylesheet
- **GIVEN** una vista del backend se renderiza dentro de `AdminLTE`
- **WHEN** se cargan los assets del panel
- **THEN** el stylesheet del backend no incluye `@tailwind base`
- **AND** la paginacion y otros componentes bootstrap del panel no quedan alterados por resets globales
