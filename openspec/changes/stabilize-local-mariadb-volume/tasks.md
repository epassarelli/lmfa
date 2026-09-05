## 1. Respaldo y configuracion

- [ ] 1.1 Crear un dump de solo lectura del volumen local actual.
- [x] 1.2 Configurar `db` para usar un volumen nombrado de Docker, conservando
  `database_local` sin cambios.
- [x] 1.3 Documentar la reconstruccion, validacion y reversion local.

## 2. Recuperacion controlada

- [x] 2.1 Recrear solo el servicio `db` sobre el volumen nombrado.
- [x] 2.2 Verificar conectividad, historial de migraciones y ausencia de DDL
  pendiente antes de reanudar el uso del entorno.
- [ ] 2.3 Aplicar migraciones y datos demo locales, y validar la suite de
  regresion focalizada.
