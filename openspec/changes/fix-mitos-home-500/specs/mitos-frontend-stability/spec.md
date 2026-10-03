## ADDED Requirements

### Requirement: La home publica de mitos debe responder sin errores del servidor
El sistema SHALL responder exitosamente la URL publica de la home de `Mitos y leyendas` sin depender de variables inexistentes en tiempo de ejecucion.

#### Scenario: Solicitud a la home de mitos
- **WHEN** un usuario solicita `/mitos-y-leyendas-argentinas`
- **THEN** el sistema responde sin error `500`

### Requirement: Los enlaces publicos del modulo deben resolver nombres de ruta validos
El sistema SHALL generar enlaces del modulo `Mitos y leyendas` usando nombres de ruta definidos en `routes/web.php`.

#### Scenario: Render de enlace hacia el detalle de un mito
- **WHEN** una vista o componente renderiza un enlace hacia el detalle de un mito
- **THEN** el enlace usa un nombre de ruta publico valido del modulo
