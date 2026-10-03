## 1. Relevamiento e integración existente

- [x] 1.1 Inspeccionar rutas públicas, layout frontend, middleware CSRF y convenciones de tests para ubicar la implementación sin colisiones.
- [x] 1.2 Inspeccionar integración OAuth/Facebook/Socialite, `config/services.php`, `.env.example`, modelos y migraciones reales para identificar cómo se vincula Facebook con usuarios locales.
- [x] 1.3 Identificar el correo de contacto vigente y los terceros/cookies realmente usados para redactar las páginas legales sin afirmar prácticas no verificadas.

## 2. Páginas legales públicas

- [x] 2.1 Implementar rutas públicas explícitas para `/privacidad`, `/condiciones`, `/eliminacion-de-datos` y `GET /deleteuserdata` en orden seguro frente a rutas dinámicas.
- [x] 2.2 Crear o adaptar controlador frontend para renderizar páginas legales con layout público, títulos, metadescripciones y canonical sobre el dominio `.com`.
- [x] 2.3 Crear vistas Blade separadas y mantenibles para privacidad, condiciones y eliminación de datos con contenido alineado al esquema e integraciones reales.

## 3. Flujo Meta de eliminación de datos

- [x] 3.1 Crear persistencia dedicada para solicitudes de eliminación con índices, estados, código de confirmación robusto y datos minimizados.
- [x] 3.2 Implementar `POST /deleteuserdata` con validación criptográfica de `signed_request`, manejo seguro de errores e idempotencia.
- [x] 3.3 Implementar el procesamiento transaccional de desvinculación/eliminación o anonimización de datos de Facebook sin romper relaciones editoriales.
- [x] 3.4 Implementar `GET /deleteuserdata/status/{confirmationCode}` con exposición pública mínima y `404` para códigos inexistentes.
- [x] 3.5 Excluir solo `deleteuserdata` de CSRF y aplicar rate limiting o protección equivalente sin desactivar la protección global.

## 4. Configuración y dominio canónico

- [x] 4.1 Verificar callback OAuth real y reemplazar hardcodes de `.com.ar` o `www` vinculados al flujo Meta por configuración basada en `APP_URL` donde corresponda.
- [x] 4.2 Ajustar `config/services.php` y `.env.example` para asegurar variables de Facebook consistentes con el dominio canónico `.com` sin tocar secretos reales.
- [x] 4.3 Documentar las URLs exactas que deberán cargarse manualmente en Meta tras el despliegue.

## 5. Validación y cierre

- [x] 5.1 Agregar pruebas automáticas de páginas legales públicas, callback firmado, persistencia, idempotencia, estado público, CSRF acotado e integridad editorial.
- [x] 5.2 Ejecutar `php artisan test` y cualquier validación adicional aplicable, registrando bloqueos si aparecieran.
- [x] 5.3 Actualizar `project/docs/00_estado_actual.md` si corresponde y preparar instrucciones de despliegue, migración, cache y verificación manual con `curl`.
