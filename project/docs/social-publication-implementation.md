# Publicaciones sociales: implementación local — 2026-11-10

## Autorización y entorno

El usuario autorizó implementar el cambio `social-publication-ledger-n8n` en la rama activa (main), sin crear rama, hacer commit/push/despliegue ni publicar externamente. Tras mostrar `migrate:status` pendiente, autorizó explícitamente migración local en `mfa` y `mfa_test`, y pruebas con transacciones en la base existente `mfa_test`.

Se preservaron los cambios previos de estado y el directorio de templates. Se iniciaron los contenedores existentes lmfa-app-1 y lmfa-db-1. PHP se ejecutó como www-data. MariaDB verificada: 10.8.8, InnoDB/utf8mb4. El inventario local previo mostró 0 requests, 0 targets y 0 attempts; no se generaliza a producción.

La primera aplicación falló en el ALTER de requested_by por reutilización de nombre de FK. Las fases aditivas habían quedado creadas. La revisión automática rechazó el reintento sin inspección; se inspeccionaron columnas y constraints de ambas bases, se verificó CASCADE original intacta y se completó usando un nombre nuevo y omitiendo estructuras ya creadas. Ambas ejecuciones finalizaron DONE. No se borraron filas, tablas ni respaldos. El down rechaza revertir automáticamente historial.

## Resultado implementado

- Tabla de habilitaciones por contenido y destino; no se agregan booleanos a 12 tablas.
- Extensión de targets/attempts de Pasarela, identidad única por contenido/canal/destino y por publicación remota; autor nullable/SET NULL para conservar resultados.
- Servicio de reserva transaccional, snapshot editorial acotado, lease SHA256, begin exclusivo y callback idempotente. Éxito monotónico y uncertain sin reenvío automático. Contador separado de envíos iniciados, máximo 3.
- Guard institucional en dispatch, job y retry de Pasarela, independiente de la bandera enabled; redes propias con otra identidad y native_portal siguen su flujo anterior. Success/published omiten ejecución. Se impide usar otra SocialAccount con el mismo ID externo para eludir el guard.
- API `/api/v1/social-publications`: candidatos paginados, claims, begin, result y consulta de un destino; PAT real sin wildcard, permisos explícitos, operador no administrador configurado. No se reutiliza Ingesta y no se crean usuarios/permisos/tokens automáticamente.
- Panel `/admin/social-publications`, solo administrador: filtros por tipo/estado en el único destino piloto, habilitación/retirada y conciliación/reintento supervisados con auditoría. No se tocaron formularios existentes de Noticias/Eventos.
- Comandos de inventario legacy readonly, backfill dry-run por defecto, emisión explícita de PAT y vencimiento de reservas sin red. No se agregó cron ni se ejecutaron opciones de escritura/emisión de credenciales.
- Export n8n inactivo, separado de diagnósticos existentes, con gate de render real, Meta sin retry y callback con retry; no importado ni probado dentro de n8n. Clasificación probada en Node con seis respuestas simuladas.
- Logs/payloads nuevos de conectores comunes saneados sin limpieza del histórico. Sin dependencias nuevas, IA ni costos de tokens.

## Archivos afectados

| Área | Archivos principales |
|---|---|
| Configuración | `.env.example`, `config/social_publication.php`, `config/adminlte.php` |
| Persistencia | migración `2026_11_10_100000_add_social_publication_ledger.php`, modelos SocialPublicationEligibility, PublicationTarget, PublicationAttempt |
| Dominio | SocialPublicationLedgerService, SocialPublicationGuard, PublicationPayloadSanitizer |
| Coexistencia | PublicationService, PublishToProviderJob, Pasarela/PublicationRequestController, BaseConnector |
| API/panel | middleware SocialPublicationOperator, dos controladores SocialPublicationController, rutas social-publications/api/admin, vista backend/social-publications/index |
| Operación | cuatro comandos SocialPublication*, `resources/integrations/n8n/` |
| Pruebas | `tests/Feature/SocialPublicationLedgerTest.php` |
| Documentación | OpenSpec aplicable, estado, migraciones, endpoints, este informe y README versionable del flujo |

OpenSpec y parte de project/docs están ignorados por configuración preexistente del repositorio. No se alteró .gitignore ni se forzó su inclusión. La guía versionable del flujo contiene el contrato operacional para que no dependa solo de archivos ignorados.

## Validación

- Migración aplicada exclusivamente por path en ambas bases autorizadas, sin ejecutar otras migraciones.
- Pruebas Feature nuevas con comprobación explícita `getDatabaseName() === mfa_test`, DatabaseTransactions, Queue::fake y Http::fake/preventStrayRequests; no RefreshDatabase ni seeders/reset.
- Suite afectada: SocialPublicationLedgerTest, PublicationTargetTest, PublishToProviderJobTest, PublicationRequestTest, EventReminderJobTest.
- Sintaxis PHP de servicios, comandos, controladores, middleware, job y migración correcta; ocho rutas registradas.
- API probada incluyendo 401/403/404/409/422/503, PAT wildcard/cookie rechazados, permiso por destino, resultado con automatización pausada, snapshots y assets, fechas futuras/vencidas, éxito monotónico y conservación al borrar usuario. 429 configurado con throttle; no se sometió a prueba de carga.
- Backfill e inventario ejecutados en modo readonly/dry-run: 0 revisables, 0 bloqueados. No se aplicó backfill DML.
- Consultas de candidatos: carga por lote de ambos tipos y legacy; test comparativo 1 versus 6 noticias sin crecimiento por fila. El panel usa eager loading de content polimórfico y paginación 15. No se midió rendimiento de producción.
- Node valida JSON, código de nodos, ausencia de IDs de credenciales, flujo inactivo/sin scheduler, no retry Meta y seis clasificaciones simuladas. No valida la importación del schema por la versión instalada de n8n ni la entrega a Meta.

## Pendientes de integración y activación

La implementación local del portal puede revisarse y conectarse al flujo. No se declara desplegada ni activada. Falta probar concurrencia física con dos conexiones independientes (la exclusión secuencial y los índices únicos sí están probados), revisión visual del panel en navegador/móvil, importación y ejecución del flujo en n8n, render en Docker/alojamiento público y credencial PAT dedicada. Las plantillas v4 están aprobadas visualmente pero el renderer Windows no está validado en Linux.

Se usa fecha local de confirmación del servidor como published_at; no se inventa fecha remota. El hash editorial no incluye el binario: el asset_reference y asset_sha256 quedan congelados por separado al aceptar begin y guardados en el intento. La API no descarga ni comprueba por sí misma el contenido del asset: el render/alojamiento de confianza debe verificarlo antes del envío. URLs públicas se construyen usando rutas existentes y APP_URL, que debe estar correctamente configurado.

La reserva/resultado incierto protege la exclusión local, no garantiza exactly-once remoto. El reconciliador aún no está programado. No hay republicación/reset de éxitos. Ante histórico ambiguo o cuenta eliminada sin identidad recuperable, conciliar manualmente y no deducir un destino.

No se modificaron URLs públicas, slugs, metadatos ni indexabilidad. El costo nuevo se limita al backoffice/API autenticada e índices de escritura; sin dependencias globales ni JS público. No se atribuyen resultados de tráfico al desarrollo local.

Resultado final de suite afectada: **67 tests pasaron, 185 assertions**, 30 tests nuevos. Seis escenarios de clasificación del export pasaron. Advertencia OPcache duplicado preexistente del contenedor; la caché PHPUnit no se usó tras advertencia de permisos inicial. Sin fallos pendientes en la suite ejecutada.

## Continuación del 2026-11-10

Usuario eligió solo Noticias por ahora. Se implementó render GD con fuente aprobada configurable y copias runtime locales, endpoint POST /{target}/render, PNG privado y referencia sha256 verificable por el mismo intento. Esto reemplaza la dependencia de alojamiento público para Facebook: n8n conserva el binario y envía multipart source. No se redistribuyeron fuentes del sistema. Las pruebas de render omiten solo en CI sin fuentes; local ejecutó las cuatro sin skip. Guardia DB local mfa_test se conserva; CI usa su base efímera del workflow existente.

71 tests pasaron (199 assertions), 34 del ledger/render. Dos flujos importados en n8n inactivos y bloqueados, solo Noticias con trigger 10:00 Argentina preparado. Eventos sin trigger horario. Se verificó export real posterior al import, referencias Meta y estados; no revisión visual del editor. Flujo con URLs exclusivamente loopback ejecutado en n8n con éxito, rechazo inequívoco, 5xx, pérdida de respuesta Meta, callback perdido y begin perdido. Cada envío simulado recibió PNG correcto; nunca hubo segundo POST Meta, y begin perdido produjo cero POST. El simulador temporal se cerró al terminar. Evidencia JSON en project/artifacts/social-n8n-protocol-evidence.json.

Datos públicos reales leídos por API: noticia833 y evento392, sin cambios editoriales. Renderer produjo ambas placas, inspeccionadas visualmente: logo/solapa/paleta v4, título entero y fecha/lugar de evento. No son publicaciones autorizadas. Diagnóstico GET de Meta ejecutado confirma Edu Passa, ID de usuario; falta convertir/verificar token de Página, no basta que un GET por ID de página funcione. Producción /api/v1/social-publications/candidates devuelve 404: código no desplegado. No se emitió PAT, otorgaron permisos ni habilitaron contenidos. Solicitudes al usuario pendientes sobre credencial y modo de despliegue.

Paquete revisable de 31 archivos con manifest.sha256.json: project/artifacts/social-publication-release-20261110.zip (sin .env, fuentes, almacenamiento, datos o credenciales). No hubo commit, push, merge, deploy ni migración productiva. ACTIVATION.md contiene pasos, alcance y confirmaciones necesarias; la programación no equivale a selección editorial automática: debe aprobarse política para que nuevas noticias entren a la cola por defecto.

## Autorización y entrega de credencial — 2026-11-10

El usuario autorizó operador técnico y token en n8n, y delegó el deploy en sí mismo. Preparada provisión CLI dry-run por defecto y --apply idempotente, dos permisos sin roles y sin emitir PAT; rechaza reutilizar identidades con otro uso. Exportación --n8n-file crea archivo privado exclusivo y evita mostrar el token, falla sin emitirlo si el archivo ya existe, revoca el intento nuevo si la escritura falla. Cinco tests nuevos pasaron (29 assertions) en mfa_test y storage temporal aislado. Se verificó la ayuda real de n8n import:credentials, incluido projectId.

No se creó un operador/token local que no serviría para producción. El usuario ejecutará provisión tras su deploy y migración del servidor; archivo privado de transferencia se importa en n8n con ID previsto MfaSocialPortal1, ya referenciado por los borradores bloqueados. Autorización vigente no implica deploy ejecutado ni credencial real emitida/importada. Se conserva la credencial de Ingesta sin cambios.

Validación conjunta final tras preparar el operador: 76 tests pasaron (228 assertions), sin skips locales ni publicaciones externas.

## Preparación de commit

El usuario autorizó hacer el commit y actualizar changelog/documentación antes de la subida. Se conserva la rama main activa, sin push ni despliegue. Changelog Unreleased y estado vigente actualizados; contrato versionable en project/docs/specs/social-publication-ledger-n8n.md, sin alterar la exclusión de OpenSpec. Se incluyen las fuentes de diseños aprobados del mismo subproyecto; no se incluyen artefactos, fuentes del sistema, almacenamiento, bases ni secretos.
