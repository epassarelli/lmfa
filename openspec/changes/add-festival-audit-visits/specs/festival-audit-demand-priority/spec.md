## ADDED Requirements

### Requirement: Priorización editorial por demanda
El auditor de Festivales SHALL ordenar los resultados por prioridad editorial, menor score, mayor cantidad de visitas y menor ID como desempate final estable.

#### Scenario: Festivales con deuda equivalente
- **WHEN** dos festivales tienen igual prioridad y score pero distinta cantidad de visitas
- **THEN** el festival con más visitas aparece primero

#### Scenario: Festivales completamente empatados
- **WHEN** dos festivales tienen igual prioridad, score y visitas
- **THEN** el festival con menor ID aparece primero

### Requirement: Visitas visibles y exportables
El auditor SHALL incluir el contador normalizado de visitas tanto en la tabla de deuda como en cada fila CSV.

#### Scenario: Exportación CSV
- **WHEN** el operador ejecuta el auditor con `--csv`
- **THEN** el archivo contiene una columna `visits` con el contador de cada festival

### Requirement: Auditoría sin escrituras
La incorporación de visitas MUST conservar el comportamiento read-only del comando.

#### Scenario: Ejecución del auditor
- **WHEN** se ejecuta el auditor con o sin CSV
- **THEN** ningún campo del festival, incluido `visitas`, resulta modificado
