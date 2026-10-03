## Context

El portal Mi Folklore Argentino necesita exponer páginas legales públicas y cumplir el flujo de eliminación de datos exigido por Meta/Facebook para una integración OAuth ya existente. El cambio toca frontend público, configuración de servicios, middleware de seguridad, persistencia nueva y posible saneamiento de asociaciones sociales/sesiones, por lo que conviene fijar decisiones antes de implementar.

Las restricciones operativas del proyecto son relevantes:

- No modificar código sin spec aprobada por el usuario.
- No inventar tablas, columnas ni relaciones; la eliminación debe adaptarse al esquema real.
- No romper contenido editorial ni claves foráneas al procesar una baja.
- Mantener foco en performance, UX y SEO desde el diseño inicial.

## Goals / Non-Goals

**Goals:**

- Publicar páginas legales accesibles, indexables de forma controlada y consistentes con el layout público actual.
- Implementar un callback `POST /deleteuserdata` verificando criptográficamente `signed_request` con el secreto configurado en `services.facebook`.
- Registrar cada solicitud con trazabilidad mínima, estado y código de confirmación suficientemente robusto.
- Ejecutar la desvinculación o anonimización de datos de Facebook en forma idempotente y transaccional.
- Exponer una página pública de estado que no filtre identidad ni permita enumeración útil de usuarios.
- Dejar documentadas las URLs exactas que se deberán cargar manualmente en Meta.

**Non-Goals:**

- Rediseñar el flujo completo de autenticación social más allá de la alineación con el dominio canónico.
- Borrar automáticamente contenido editorial publicado si su conservación es necesaria para integridad histórica o relacional.
- Implementar un centro de privacidad general para todos los proveedores externos.
- Modificar producción, credenciales reales o configuración remota de la aplicación en Meta.

## Decisions

### 1. Separar la funcionalidad en dos capacidades: páginas legales y eliminación de datos Facebook

Se modela como dos capacidades nuevas para evitar mezclar contenido estático, SEO y cumplimiento legal con el flujo transaccional de callback, persistencia y baja de datos. Esta separación permite validar cada parte con tests acotados y facilita futuras iteraciones si cambian los textos legales o el protocolo del proveedor.

Alternativa considerada:
- Hacer una sola capacidad monolítica. Se descarta porque dificulta revisar alcance y aceptar cambios parciales.

### 2. Usar rutas públicas explícitas en `routes/web.php` antes de capturas dinámicas

Las rutas `/privacidad`, `/condiciones`, `/eliminacion-de-datos`, `/deleteuserdata` y `/deleteuserdata/status/{confirmationCode}` deben declararse de forma explícita y temprana para evitar colisiones con segmentos dinámicos del frontend público. Esto preserva la semántica pública y evita falsos 404 o resoluciones sobre controladores no relacionados.

Alternativa considerada:
- Montarlas bajo un prefijo como `/legal/*`. Se descarta porque Meta y la consigna requieren compatibilidad con `/deleteuserdata` y URLs públicas concretas.

### 3. Mantener contenido principal sin dependencia de JavaScript y reutilizar layout público existente

Las páginas legales y la página de estado se resolverán como Blade server-side sobre el layout público actual. Esto reduce complejidad, evita dependencia innecesaria de JS para contenido estático y conserva consistencia visual/SEO.

Alternativa considerada:
- Resolverlas con Livewire. Se descarta porque el contenido principal es estático y el requerimiento prioriza simplicidad, robustez y render directo.

### 4. Validar `signed_request` siguiendo el protocolo de Meta y rechazar temprano

El callback deberá:

- exigir `signed_request`;
- separar firma y payload;
- decodificar con Base64 URL-safe;
- verificar `algorithm = HMAC-SHA256`;
- recalcular la firma con `config('services.facebook.client_secret')`;
- comparar con `hash_equals`.

Las solicitudes inválidas devolverán HTTP 4xx con JSON breve y sin detalles sensibles. El secreto jamás se registrará en logs.

Alternativas consideradas:
- Confiar en IPs de Meta. Se descarta porque no cumple el requisito criptográfico.
- Aceptar payload sin validar el algoritmo. Se descarta por riesgo de falsificación.

### 5. Persistir solicitudes con una tabla dedicada y datos minimizados

La tabla de solicitudes almacenará solo lo necesario para trazabilidad e idempotencia: proveedor, código de confirmación único, referencia local segura, hash o referencia mínima del identificador externo si hace falta, estado y timestamps operativos. No se guardará `signed_request`, payload completo, tokens ni secretos.

Alternativa considerada:
- No persistir y responder en línea. Se descarta porque Meta requiere una URL de estado y el proyecto necesita auditoría mínima.

### 6. Procesar la eliminación de forma sincrónica salvo que el esquema real obligue a cola

La preferencia inicial es procesar en la misma request si el trabajo real es acotado a desvincular cuenta social, tokens/sesiones y anonimización puntual, porque reduce complejidad operativa y evita dependencia de worker para un flujo probablemente de bajo volumen. Si durante la inspección del esquema se detecta que la baja implica múltiples relaciones pesadas o jobs ya existentes, se podrá mover a cola y documentar la necesidad de worker.

Alternativa considerada:
- Forzar siempre un Job. Se descarta por complejidad extra si el proceso real es pequeño e inmediato.

### 7. Preservar contenido editorial mediante desvinculación o anonimización, no borrado ciego

La eliminación apuntará a la identidad y credenciales de Facebook, no al contenido editorial público. Si el usuario local tiene relaciones necesarias para artículos, noticias, biografías u otros recursos, se priorizará desvincular la autenticación social, invalidar sesiones y anonimizar campos personales no imprescindibles, manteniendo integridad relacional.

Alternativa considerada:
- Eliminar completamente el usuario. Se descarta hasta no confirmar que ninguna relación editorial quede rota.

### 8. Excluir solo `deleteuserdata` de CSRF y cubrir el endpoint con validación/rate limiting puntual

El callback necesita aceptar POST externos, por lo que se exceptuará únicamente la ruta histórica requerida. El resto de la protección CSRF permanecerá intacta. Se añadirá rate limiting razonable y manejo controlado de excepciones para reducir abuso sin bloquear el uso legítimo.

Alternativa considerada:
- Desactivar CSRF por grupo o middleware completo. Se descarta por ampliar innecesariamente la superficie de riesgo.

## Risks / Trade-offs

- [El esquema social real podría no coincidir con convenciones típicas de Socialite] → inspeccionar primero modelos, migraciones y flujo OAuth antes de codificar la eliminación.
- [Los textos legales podrían afirmar prácticas no confirmadas] → redactar solo sobre integraciones/cookies/terceros verificables y marcar explícitamente cualquier punto a validar por el equipo.
- [Borrar o anonimizar un usuario podría afectar autoría o claves foráneas] → encapsular en transacción, basarse en relaciones reales e incluir tests que verifiquen integridad editorial.
- [El dominio canónico puede estar hardcodeado en más de un punto] → reemplazar solo referencias vinculadas al flujo Meta/OAuth por configuración basada en `APP_URL` o helpers de URL.
- [El callback podría ser invocado repetidas veces por Meta] → usar clave de idempotencia derivada del usuario/proveedor o lógica de reproceso seguro con estados terminales reutilizables.

## Migration Plan

1. Confirmar esquema real de autenticación social, usuarios y sesiones.
2. Agregar nueva persistencia para solicitudes de eliminación si no existe equivalente.
3. Implementar rutas públicas, controlador y vistas legales/estado.
4. Implementar verificación del callback y procesamiento de baja.
5. Ajustar CSRF/configuración de servicios/env example según corresponda.
6. Agregar pruebas automáticas de páginas legales, callback, estado e integridad editorial.
7. Ejecutar suite de tests local y documentar despliegue.

Rollback:

- Revertir la migración nueva si el despliegue aún no procesó datos productivos.
- Revertir rutas/controlador/vistas del cambio y restaurar configuración previa de Meta/OAuth si hubiera sido tocada.

## Open Questions

- Qué columnas exactas guardan la relación entre cuenta local y Facebook en este proyecto.
- Si el portal conserva email/tokens/avatar/nombre provenientes de Facebook o solo los usa al autenticar.
- Qué correo de contacto vigente debe exponerse en las páginas legales.
- Si existen obligaciones editoriales explícitas para conservar autoría nominal o si debe anonimizarse ante la baja.
