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

## Pruebas de producción posteriores al push informado

El usuario informó que hizo push y corrió migraciones sin error; no se ejecutaron migraciones productivas desde el agente ni se asume evidencia directa del esquema. Ocho comprobaciones HTTPS pasaron: home y noticia833 = 200; GET candidates, GET estado y POST claims/render/begin/result sin autenticación = 401. Las solicitudes POST fueron vacías y rechazadas por autenticación antes de efectos de dominio; no habilitaron contenidos, reservaron intentos ni publicaron. Endpoint antes 404 ahora disponible/protegido.

Lista de credenciales n8n revisada mediante UI: credencial dedicada MFA - Publicaciones Portal todavía ausente; solo las credenciales MFA previas están disponibles. No se usó Ingesta para ejecutar el nuevo contrato. La revisión automática rechazó exportar la credencial para comprobar su existencia; se cambió a lista UI sin extraer secretos. Borrador Noticias exportado sin credenciales: active=false, allow_publish=false, retry Meta=false. Pendientes: salida preflight productivo, provisión autorizada de operador y transferencia privada del PAT, fonts/GD reales y prueba autenticada. No se publica ni activa por la sola migración.

## Credenciales productivas verificadas — 2026-11-11

El usuario aportó preflight productivo: PHP8.2.35, GD, fuentes legibles, tablas/columnas ledger y HTTPS correctos, enabled=false, operator_count=1. Informó provisión del operador453 y entregó localmente el JSON privado de credencial generado en ese servidor. Se verificó que storage/app/private/social-publications-n8n.json está ignorado por Git. Se importó la única credencial autorizada en n8n, sin mostrar el token ni usar Ingesta.

Nuevo diagnóstico inactivo MfaLedgerRead01 ejecutado desde n8n contra producción: candidates503 (pausa), consulta de estado inexistente404 y claims{}503 (pausa). Resumen confirma permisos read/execute, servicio pausado, cero claims creados y cero POST Meta. El POST se hizo solo tras constatar la pausa y sin payload válido.

Meta: GET id/name confirmó primero User/Edu Passa y la lectura de la Página conocida. Una consulta a me/accounts fue rechazada por revisión automática ante posible respuesta con secretos; no se ejecutó. Se pidió autorización específica para obtener token de la Página145931542128207 y guardarlo solo en su credencial n8n. El usuario autorizó. Consulta específica por ID procesada en memoria y archivo privado0600 de transferencia, sin mostrar token ni persistir respuesta cruda. Importada en la credencial Meta existente; GET /me posterior devuelve Mi Folklore Argentino, ID145931542128207, HTTP200. Sin cambios de permisos ni publicaciones.

Retiradas únicamente las dos copias temporales privadas creadas por el agente dentro de n8n tras verificaciones; original local del usuario conservado. Flujo auxiliar de obtención reemplazado por estado cerrado sin consultas de tokens. Pendiente enabled=true del portal para probar render real y habilitación/publicación concreta; workflows de publicación siguen inactivos/bloqueados. No se hizo commit/push adicional de estas notas.

## Render real y noticia autorizada — 2026-11-11

Usuario confirmó enabled=true y autorizó explícitamente habilitar noticia833 con override portal_only solo para la prueba, sin publicar todavía. Mediante sesión admin se habilitó el contenido; panel confirmó pending, envíos0. Nuevo workflow MfaNewsRender01 contiene solo candidates/claim/render (sin begin/Meta/result), ejecutado con credencial dedicada. Producción devolvió target1, attempt1, PNG1080x1350, SHA2566dba8fe84ab6615509ca0e35c4cbb8d568d551e8617c1b66ef6841d8e139bf6a; copia editorial y URL verificadas, imagen inspeccionada sin desbordes. Lease solo en archivo privado dentro de n8n; PNG y resumen sin secretos en project/artifacts/social-production-news-833*.

Se preparó un borrador inactivo MfaNewsOneTest01, bloqueado hasta aprobación, sin scheduler, dirigido exclusivamente a noticia833 y con validación de hash editorial/placa antes de begin. Se mostró imagen y copy y se pidió autorización específica de UNA publicación real. La programación diaria sigue inactiva/bloqueada. Intento de cerrar reserva previa desde panel no cambió el estado: validación de checkbox y luego sesión expiró (419/login). Se seguirá el reclamo transaccional con vencimiento seguro de reserva, sin reintento Meta. No hubo begin ni POST Meta en esta etapa.

El usuario autorizó explícitamente UNA publicación real con la imagen/copy presentados. Se prepara MfaNewsOneTest01 sin scheduler y con guardas de identidad/hashes; se deja vencer la reserva de preview antes del nuevo claim. La programación diaria permanece bloqueada. No se atribuye éxito hasta respuesta remota y confirmación del portal.

## UNA publicación real autorizada y verificada — 2026-11-11

El usuario autorizó explícitamente la placa/copy mostrados de noticia833 para UNA publicación en Mi Folklore Argentino, sin activar la diaria. Tras vencer la reserva de preview, workflow one-off tomó target1/attempt2, cancelando la reserva anterior sin contar un envío. begin aceptado, único POST Meta (sin retry) devolvió HTTP200 y post145931542128207_1618780120285817; callback portal success con mismo ID. GET posterior portal200 confirma attempts_count1 y published_at2026-11-11T13:21:25Z. GET Meta200 confirma ID, imagen presente y mensaje idéntico al copy aprobado. Permalink canónico devuelto por Meta: https://www.facebook.com/1618781103619052/posts/1618780120285817.

Una prueba adicional de POST claims para intentar verificar409 fue rechazada por revisión automática por posible mutación productiva no autorizada; no se ejecutó. Alternativa readonly: GET estado sigue success/1envío y GET candidates200 devuelve data vacía, noticia833 excluida. Esto prueba exclusión de candidatos y estado, no sustituye la prueba de carrera física ni afirma un POST409 productivo observado.

One-off volvió a quedar bloqueado/inactivo. Diaria MfaNewsDailyV401 confirmada active=false y allow_publish=false; se pidió al usuario decisión explícita para 10:00 Argentina con cola habilitada individualmente. No se habilitó automáticamente ninguna otra noticia. Dos leases temporales privados propios retirados tras éxito verificado; evidencia pública en project/artifacts/social-production-post-verified.json y social-production-exclusion-verified.json. Original local de credencial del usuario se conserva ignorado en Git. No se hizo commit/push adicional durante las pruebas.

## Activación diaria autorizada — 2026-11-11

El usuario pidió expresamente «Activala a las 11 hs» en respuesta a activar Noticias con cola habilitada individualmente, sin publicaciones si está vacía y Eventos manual. Se configuró instancia MfaNewsDailyV401 con cron0 0 11 * * *, timezone America/Argentina/Buenos_Aires y gate habilitado; se publicó con n8n publish:workflow. CLI indicó recarga necesaria. Se revisaron solo metadatos de execution_entity readonly: tres filas running correspondían exclusivamente a nuestras pruebas133/134/135 ya completadas y verificadas. ps confirmó cero procesos CLI activos; se recargó solo n8n sin alterar datos/credenciales/workflows ajenos. Readiness200 y log Activated workflow MFA - Noticias - Facebook diario11:00Argentina (ID MfaNewsDailyV401) confirman carga efectiva; metadatos readonly active=true, activeVersionId presente, cron/zona correctos.

Fuente versionable builder/daily-news actualizada a11:00 pero conserva export active=false/gate=false por seguridad: una importación futura requiere revisión y activación explícita, no representa snapshot de la instancia activa. Eventos sin scheduler. Cola productiva vacía tras éxito833; no se habilitaron otras noticias. Próximas ejecuciones solo publicarán contenido habilitado por administrador y elegible. PC/Docker/n8n deben estar operativos a la hora prevista. Reconciliador periódico en Hostinger sigue pendiente de programación operativa; ante reservas estancadas se usa social-publications:reconcile, sin consultas Meta ni reenvíos de uncertain. No se realizó commit/push adicional durante la validación/activación.

## Cambio pedido: Noticias sin habilitación manual — implementación local

El usuario cambió expresamente la política: una noticia por ejecución a partir de833, activa en portal y no publicada en la red. Se usa ID>833 ascendente y estado público canónico del frontend (publishedVisible/isPublished). La primera prueba contra estado legacy reveló que esa columna puede estar desincronizada con el estado público; se corrigió la regla para usar exactamente el portal y se agregó regresión. No se modificaron datos para arreglarlo.

AutomaticNewsPublication genera selección SQL paginada sin escribir ni cargar relaciones por noticia. Excluye ledger mismo destino y legacy por Page real. Claim bloquea contenido y bootstrappea eligibility/request/target/auditoría sin habilitación humana. Locks alineados contenido→eligibility→target en enable/claim/begin para evitar orden inverso. Snapshot/begin siguen bloqueando retirada del portal y cambios de copy/placa. Eventos/manual y modos opcionales anteriores preservados. La API no gana un endpoint de habilitación genérico.

No se aplicaron migraciones ni consultas modificatorias en producción. El workflow11 existente conserva su contrato y horario; la política productiva no cambia hasta desplegar estos archivos y regenerar config. Tests transaccionales en mfa_test con APIs simuladas. Nueva configuración true/after833 por defecto, sin necesidad de editar un flag por noticia. El panel informa modo automático y reserva formulario manual a Eventos. No cambian slugs/rutas públicas/SEO ni dependencias; consultas por lote, índice PK+identidad de targets, sin N+1 observado en el test de1vs6 noticias.

Validación final automática: 90 tests pasaron (313 assertions), de los cuales14 cubren la política nueva. Sin pruebas ni mutaciones productivas de la regla todavía no desplegada. Sin migraciones nuevas. Selección readonly1vs6 mantiene número de consultas estable. Exports JSON y spec estricta validados. Se prepara commit local dentro de la autorización previa del subproyecto; sin push ni despliegue.

## Verificación posterior al despliegue de selección automática — 2026-11-11

Usuario informó deploy y ejecución config:cache/view:clear. Verificación independiente por n8n en workflow inactivo/soloGET MfaAutoReadCheck: candidates per_page1=200, candidates per_page50=200, historial target1=200. Total5 candidatas, IDs834,835,836,837,838, estrictamente ascendentes y mayores a833. Primera834: David Robles renovó diez clásicos del folklore en su disco Somos Tucumán. La833 no aparece y su historial permanece success, attempts_count1, post145931542128207_1618780120285817. No se hicieron claims/render/begin/result ni POST Meta en esta verificación. Evidencia saneada: project/artifacts/social-automatic-candidates-prod-20261111.json.

Workflow diario exportado sin secretos y comprobado active=true, gate habilitado, timezone America/Argentina/Buenos_Aires y cron0 0 11 * * *. No se reimportó/reinició ni alteró el horario durante esta comprobación. La nueva selección queda acreditada en producción; no requiere habilitación humana individual de Noticias. Eventos permanece manual. No se hizo commit/push adicional de estas notas operativas.

## Instagram Noticias — 2026-11-11 (implementado local, pendiente deploy)

Spec: `project/docs/specs/social-publication-instagram-news.md`; guía operativa y de release: `resources/integrations/n8n/INSTAGRAM.md`. Destino IG independiente en el ledger, cola basada en éxitos Facebook, JPEG público por hash, registro de container_id y permalink verificado, con bloqueo ante resultados inciertos. Workflow `MfaInstagramNews01` exportado manual/inactivo/bloqueado. Validación local: 61 tests / 298 assertions y siete escenarios n8n simulados con mock privado. Sin migraciones, sin deploy y sin publicación real en Instagram; pendiente prueba real de la noticia 833 (autorizada condicionalmente) tras el despliegue humano.
