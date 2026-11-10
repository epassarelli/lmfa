# Contrato de publicaciones sociales — social-publication-ledger-n8n

Copia versionable de los requerimientos y decisiones aprobados. OpenSpec original permanece ignorado por la configuración vigente; no se altera .gitignore. Este documento conserva la trazabilidad para revisar el código del release. Estado operativo: recursos/integrations/n8n/ACTIVATION.md y project/docs/social-publication-implementation.md. No autoriza despliegues ni publicaciones.

## Requerimientos aprobados

# Requerimientos funcionales y trazabilidad

## 1. Contexto y problema

La API entrega contenido y n8n puede publicar, pero hoy no hay una marca fiable que impida reenviar el mismo contenido a la misma cuenta. Un bool no distingue nunca enviado, envío en curso, error e incertidumbre. Ya existe Pasarela y debe reutilizarse. La aprobación de plantillas v4 es un hecho; la aprobación de cada contenido y la publicación operativa son pasos separados.

## 2. Actores afectados

- Administrador/editor MFA: habilita difusión, revisa historial y resuelve excepciones.
- Operador técnico: migra y configura con autorización, mantiene credenciales y workflow.
- n8n con PAT dedicado: reserva, prepara y reporta resultados; no decide permisos editoriales.
- Jobs/conectores legacy: respetan el mismo registro y ejecutor.
- Audiencia: recibe una publicación por destino con vínculo público válido.

## 3. Alcance y fuera de alcance

MVP: News/Eventos → Página Facebook institucional, habilitación por contenido/destino, estado único, intentos, reserva, API, panel, pruebas y workflow apagado. Preparado estructuralmente para más redes; no se implementan Instagram/X ni los otros diez adaptadores. No republicaciones automáticas, IA paga, envío de letras, cambios masivos, despliegue o envío real en la implementación local. Generador Docker y hospedaje de placas son dependencias de activación, no requisitos para probar el protocolo con fakes.

## 4. Historias de usuario

| ID | Historia | Spec / validación |
|---|---|---|
| HU-01 | Como administrador quiero habilitar un contenido por destino sin confundirlo con su estado publicado en web. | ledger / EligibilityTest |
| HU-02 | Como n8n quiero obtener únicamente candidatos aptos y reservar uno sin competir con otra ejecución. | API + ledger / ClaimTest y concurrencia |
| HU-03 | Como administrador quiero una marca de publicado solo tras confirmación, con fecha e ID externo. | ledger / ResultTest |
| HU-04 | Como operador quiero recuperar fallos sin duplicar posts si se perdió una respuesta. | ledger / RecoveryTest |
| HU-05 | Como administrador quiero consultar y resolver casos inciertos con evidencia. | operations / BackofficeTest |
| HU-06 | Como mantenedor quiero que la Pasarela y n8n no publiquen por separado el mismo destino. | ledger / LegacyGuardTest |
| HU-07 | Como responsable quiero trazabilidad del copy, placa y template aprobados. | API / SnapshotTest |
| HU-08 | Como responsable quiero un piloto reversible, sin secretos en exports y con migraciones revisables. | operations / API access tests y checklist humano |

## 5. Reglas de negocio

- Nunca publicado = ausencia de éxito confirmado. Un resultado incierto no es candidato aunque published=false.
- Un éxito por contenido/red/cuenta para el primer envío; editar título o cambiar template no borra la marca.
- Sin habilitación explícita no se publica. `portal_only` necesita override por acción administrativa con auditoría.
- Portal publicado, fecha de publicación alcanzada y URL válida son obligatorios. Agenda vigente y lugar/fecha definidos; no anunciar datos faltantes ni interpretar registros históricos como próximos.
- Success es monotónico; las redes son independientes. No autorizar otro intento de un caso incierto sin evidencia de no publicación.
- Misma confirmación repetida no crea otro intento. Una cuenta de red distinta es otro destino.
- El ejecutor se configura por destino. Los jobs Laravel no publican targets n8n.
- Templates v4 aprobados se registran por ID/versión; no se generan variantes de diseño por IA.

## 6. Criterios de aceptación

Los escenarios normativos Dado/Cuando/Entonces están en `specs/social-publication-ledger/spec.md`, `specs/n8n-publication-api/spec.md` y `specs/social-publication-operations/spec.md`. Debe existir al menos un Feature por HU, y checklist honesto para carreras/pruebas externas que el entorno no permita ejecutar.

## 7. Casos borde

Dos claims simultáneos; respuesta de claim perdida; begin perdido; Meta publica pero callback falla; timeout remoto; callback viejo; ID externo diferente; fallo después de éxito; lease reservado frente a lease con envío iniciado; edición durante render; contenido retirado; Agenda vencida; cuenta social borrada; cambio de token; registros legacy duplicados; módulo/tipo/proveedor desconocido; título que no entra; falta de asset; n8n/PC apagado; cambio de día/zona horaria; PAT revocado; intento de inyectar URL/credencial en payload o error.

## 8. Impacto técnico, editorial, SEO y performance

Técnico: extensiones aditivas de Pasarela, índices, guard central, endpoints y panel. Editorial: default deshabilitado y aprobación trazable; la disponibilidad de una plantilla no habilita todo el archivo. SEO: no modificar rutas/canonicals ni indexabilidad pública; links usan rutas reales y UTM sin alterar canonical. Performance: endpoint de candidatos paginado con máximo 50, índices compuestos y Resource mínimo; no N+1 ni carga de cuerpo/creadores por ítem; no HTTP externo dentro de locks. No atribuir mejoras productivas a tests locales.

## 9. Preguntas abiertas y dependencias

La implementación local puede avanzar con los defaults de diseño. Activación condicionada a auditoría de esquema/historial, credencial específica, destino permitido, contenido habilitado, motor/URL de placas, prueba externa autorizada, horario y alertas. Si aparecen contradicciones reales de esquema o decisiones arquitectónicas nuevas, presentar evidencia y detener el trabajo dependiente. No convertir estas dependencias en pretexto para omitir código/pruebas independientes.

## 10. Validación prevista

Feature en base de testing existente y autorizada, `DatabaseTransactions`, `Queue::fake`, `Http::fake`. Revisar configuración DB antes de ejecutar: phpunit.xml no fija DB aislada. Correr tests existentes afectados de Pasarela, News/Eventos y permisos; reemplazar RefreshDatabase antes de ejecutar cualquiera que lo use. Preflight readonly de migración, análisis de duplicados y prueba de rollback vacío solamente con permiso. Meta fake para éxito, rechazo, timeout, callback perdido y replays. Publicación real, producción, nuevas credenciales y cron requieren autorizaciones independientes.


## Diseño y continuaciones autorizadas

## Context

### Hechos comprobados en el repositorio

- `database/migrations/2026_04_09_050000_create_publication_orchestration_tables.php` crea requests, targets e intentos. Targets no tiene identidad de contenido/destino única, lease, publicado_en o control de ejecutor. Attempts ya tiene ID/URL externos y payloads.
- `PublicationService::createRequest()` crea filas y llama `dispatchJobs()` automáticamente. No usarlo sin adaptar para reservar desde n8n: dispararía otro publicador.
- `PublishToProviderJob` solo omite `status=published`, pero `BaseConnector::recordAttempt()` guarda `success`. Esa diferencia permite que un éxito se reintente. El job reintenta excepciones; no distingue envío ambiguo.
- `FacebookConnector` usa una versión Graph fija y guarda un payload con `access_token` mediante `BaseConnector`. El nuevo contrato no debe llamar ese conector ni guardar ese secreto. Sanitizar nuevas escrituras de intentos en el código común si se toca; no modificar registros históricos ni rotar credenciales en esta entrega.
- La Pasarela contempla `native_portal`, redes propias e institucionales; los modelos morph de requests usan clases PHP. No cambiar el morph map global.
- Noticias y Eventos tienen `publication_mode`, `editorial_status`, `published_at` y `HasPublicationState`. En las pruebas remotas fueron observados contenidos `published` con `publication_mode=portal_only`.
- Las 12 plantillas v4 quedaron aprobadas explícitamente por el usuario. El renderizador actual es standalone Windows; aún no hay renderizador Docker ni almacenamiento público de placas validados.
- `phpunit.xml` NO fija una base de datos aislada. No ejecutar tests hasta verificar configuración efectiva; `QUEUE_CONNECTION=sync` obliga a simular colas.

### Límites de evidencia

No se inspeccionó el esquema de la BD ni el historial real de Pasarela en esta preparación. Conteos documentados son históricos, no acreditan que hoy no existan publicaciones. Las migraciones y consultas de preflight deben contrastar esquema real antes de implementar DDL. No se publicó en Meta ni se validó una integración end-to-end productiva.

## Goals / Non-Goals

**Goals:** una marca fiable por contenido/destino; reserva concurrente; confirmación idempotente; incertidumbre conservadora; autorización editorial explícita; panel visible; contrato n8n y trazabilidad de plantilla.

**Non-Goals:** publicaciones externas durante implementación, migraciones ejecutadas sin confirmación, republicación periódica, borrado de posts, corrección automática de contenido, IA paga, nuevas redes activas, doce adaptadores funcionales, migración masiva de datos o refactor completo de Pasarela. Noticias/Eventos + Facebook institucional constituyen el MVP. Renderizador Docker y hosting de imágenes son otro entregable.

## Decisions

### 1. Extender la Pasarela y mantener un registro por destino concreto

Clave lógica: `(content_kind, content_id, provider, destination_key)`. Aliases permitidos MVP `news` y `events`; destino canónico `facebook:page:<ID>`, calculado por MFA desde configuración, nunca una URL arbitraria enviada por el cliente. El ID público conocido de la Página se configura por el operador; no es un secreto. Facebook e Instagram, y dos cuentas de la misma red, necesitan estados independientes.

Agregar columnas de identidad nullable a `publication_targets`, conservar request y attempts. El índice único será sobre `ledger_content_kind(32), ledger_content_id(unsigned bigint), provider(existente 50), destination_key(96)`. Sin índices parciales por prefijo; presupuesto aproximado 720 bytes en utf8mb4 más overhead: verificar capacidad real de MariaDB/InnoDB antes del DDL. No decidir un índice alternativo silenciosamente si falla. La clave es inmutable después del primer intento; `social_account_id` sigue opcional y eliminar esa cuenta no borra el historial.

Las filas legacy sin identidad siguen legibles. No asumir que NULL evita duplicados de filas viejas: el preflight y el guard compartido de envío deben resolverlas antes de habilitar el destino. Sin registro equivale a `never_published`; no se precargan doce redes ni se crean bools en los contenidos. `published=false` significa que no hay éxito confirmado, NO que sea seguro reintentar un resultado incierto.

Alternativa descartada: tabla paralela de envíos más bools por recurso. Duplicaría los intentos y haría más difícil coordinar Pasarela y n8n.

### 2. Separar habilitación editorial y resultado

Nueva tabla propuesta `social_publication_eligibilities`: `id`, `content_kind(32)`, `content_id`, `provider(16)`, `destination_key(96)`, `enabled` default false, `portal_only_override` default false, `enabled_by`/`disabled_by` usuarios nullable con nullOnDelete, `enabled_at`/`disabled_at`, timestamps. Unique por clave lógica. No cascada desde la cuenta/usuario hacia historial de publicación. Auditoría en audit_logs existente, sin payloads privados.

Auditoría existente comprobada: reutilizar `AuditLog`/`audit_logs` (migración `2026_04_09_053000_create_templates_notifications_audit_tables.php`) con actor, entidad, acción, estados anterior/nuevo y motivo saneado. No crear una segunda tabla de auditoría. No usar su helper dependiente de request desde un comando sin contexto; persistir actor explícito cuando corresponda.

Una fila habilitada por administrador permite el destino. Para `portal_only` se exige además una acción explícita que confirme `portal_only_override=true`; conservar el campo original. El panel debe explicar que la habilitación agrega difusión institucional para ese destino. Ningún token n8n puede habilitar, aprobar ni cambiar publication_mode. `social_only` no hace candidato un contenido no publicado en web: el objetivo del piloto es llevar visitas a una URL pública.

Habilitar crea una solicitud social y un target `pending` mediante el servicio del ledger sin dispatch. Sin fila habilitada no hay candidatos, aunque el contenido tenga `full`. Inhabilitar cancela una reserva no iniciada; si ya está enviándose no se puede garantizar detener Meta y debe conservarse evidencia de la carrera. No crear éxito ni borrar historial al deshabilitar. Rehabilitar un éxito no lo vuelve a enviar.

Por defecto se incorporan Noticias; Agenda puede habilitarse explícitamente por contenido, con `isPublished()`, URL pública y fecha vigente (`end_at` si existe, o `start_at` si es de una sola fecha). Una fecha sin hora no autoriza inferir horarios. Contenidos borrados, retirados, pendientes o vencidos quedan excluidos. No habilitar masivamente el archivo durante migración.

### 3. Máquina de estados compatible con los nombres existentes

Targets nuevos: `pending → reserved → processing → success|failed|uncertain`; `cancelled` para retiro antes de envío. Lectura externa muestra `success` (y el legacy `published`) como `published`. Mantener las constantes/mapeos explícitos en un único lugar y documentar API; evitar mezclar `published`, `success` y `completed` entre entidades.

Requests conservan estados existentes y se recalculan desde sus targets sin afirmar `completed` mientras haya un target incierto. Attempts: `reserved`, `processing`, `success`, `failed`, `uncertain`, `cancelled`. Esto no cambia los estados de tareas/backlog. `attempt_number` cuenta reservas de forma monotónica; `attempts_count` para el límite de reenvíos solo aumenta cuando begin acepta un nuevo envío. Replays y reservas canceladas no aumentan el contador de envíos.

Campos propuestos targets: `executor` default `laravel` (nuevos ledger `n8n`), `published_at`, `external_post_id`, `external_url`, `lease_token_hash(64)` oculto, `lease_expires_at`, `retry_after`, `attempts_count` default 0, `last_error_code`, `last_error_message` saneado, `content_hash(64)`, `payload_snapshot_json`, `template_id(64)`, `template_catalog_version(16)`, `asset_sha256(64)`/`asset_reference` opcionales. No almacenar tokens Meta ni URLs con access_token. Único ID externo por provider/destino no puede confirmar dos contenidos distintos.

Campos propuestos attempts: `request_key(36)` UUID unique nullable (idempotencia de claim), `executor`, `n8n_execution_id` opcional (metadato, no identidad confiable), `lease_expires_at`, `dispatch_started_at`, `outcome_fingerprint(64)` nullable. Unique `(publication_target_id, attempt_number)` tras preflight; el contador se asigna bajo lock, no con `count()+1` sin bloqueo. El lease es 256 bits aleatorios, solo hash persistido; se entrega una vez. Nunca aparece en GET de historial.

Preservación adicional necesaria: la migración existente define `publication_requests.requested_by` con cascadeOnDelete, lo que hoy puede eliminar request/targets/attempts al borrar un usuario. Preparar una migración revisable para volver requested_by nullable y FK nullOnDelete, conservar datos y manejar requester ausente en servicios/vistas de Pasarela tocados. No asignar un usuario ficticio para rellenar historia. Si cambiar nullabilidad exige soporte ausente en el entorno, preparar DDL nativo MySQL/MariaDB revisable, sin instalar DBAL automáticamente. Probar la eliminación transaccional de un usuario de fixture conservando request/targets/attempts y sin envío. Down no debe forzar NOT NULL si quedaron filas sin requester: bloquear esa reversión y usar el plan de pausa/conservación, sin inventar autorías.

### 4. Claim y begin separados para evitar reintentos a ciegas

Transacción de claim: validar ejecutor/destino habilitado, bloquear target con `lockForUpdate`, revalidar contenido/habilitación, insertar attempt `reserved` y actualizar target. Índice unique arbitra creaciones concurrentes; capturar violación de unique y releer bajo lock, no despachar antes de commit. No mantener transacción abierta durante render o HTTP Meta.

El Idempotency-Key de claim identifica un intento, no una publicación nueva. Repetirla devuelve el mismo attempt sin crear otro ni rotar lease. No se puede recuperar el secreto de lease después de perder la primera respuesta: devolver metadatos sin secreto, esperar vencimiento seguro de reserva. Éxito previo devuelve 409 `ALREADY_PUBLISHED`; reserva ajena activa devuelve 409 `LEASE_HELD`. Una reserva de 10 minutos sin begin puede recuperarse; cancelar el intento viejo y generar uno nuevo con token distinto.

Antes del POST a Meta, n8n debe llamar begin con lease e ID de attempt. MFA verifica vigencia, snapshot y habilitación; luego marca `processing` y registra `dispatch_started_at`. Es obligatorio esperar la respuesta de begin antes del envío. Si se pierde la respuesta de begin, n8n NO envía ni reintenta el envío; consulta estado y deja resolver incertidumbre. Un begin repetido responde el estado pero no concede otro permiso para POST.

Después de begin, lease vencido o caída del worker pasan a `uncertain`, NUNCA a pendiente por tiempo. Un viejo worker no obtiene otro begin con token vencido; no existe fencing perfecto sobre una API externa, por lo que no se autoriza otro intento mientras exista una posibilidad de que el primero publique. MFA no promete exactly-once de Meta: ofrece exclusión local y bloqueo conservador ante resultados ambiguos.

Un reconciliador local de estados (comando Artisan explícito) puede marcar reservas expiradas y operaciones inciertas; no contacta redes ni habilita nuevos envíos. Instalarlo en scheduler solo tras configuración revisada. Se debe poder evaluar expiración también en claim sin depender de cron activo.

### 5. Confirmación idempotente y éxito monotónico

Result acepta `success`, `definitive_failure`, `uncertain`, lease+attempt, hash del snapshot y los metadatos mínimos. `success` requiere ID externo no vacío y coherente con provider/destino; guardar fecha de confirmación del servidor y fecha remota opcional diferenciadas. Un timeout, conexión cortada o 5xx tras iniciar POST es incierto salvo evidencia inequívoca de que no se creó nada. No clasificar automáticamente cualquier 4xx como seguro sin examinar el contrato del proveedor.

Repetir mismo resultado/ID devuelve 200 sin otro intento; distinto ID o contenido/lease no coincidente da 409 y auditoría. Un fallo tardío no sobrescribe `success`. Un éxito tardío del mismo intento que quedó incierto sí puede confirmarlo si coincide con la identidad/snapshot y no hay resolución contradictoria; se audita. Los callbacks nunca invocan Meta. Si falló guardar el éxito, n8n reintenta SOLO el callback con el mismo ID, no el POST remoto.

Solo fallos confirmados como anteriores al envío o rechazos inequívocos entran a retry, con backoff (60 s, 5 min, 30 min), máximo tres intentos; luego revisión humana. No reiniciar contador al editar el contenido. Hash de título/copy/URL/template/asset sobre JSON canónico congelado; si cambia el contenido antes de begin, conflicto y preparación nueva sin enviar; después de begin preservar snapshot y guardar discrepancia para revisión.

### 6. Contrato API y credenciales acotadas

Nuevas rutas propuestas bajo `/api/v1/social-publications`:

| Método / ruta | Uso / respuesta |
|---|---|
| GET `/candidates?provider=facebook&destination_key=...&content_kind=news&per_page=15` | Solo elegibles, no éxito/reserva/envío incierto; resumen público mínimo. Paginación límite 50, filtros allowlist. |
| POST `/claims` | Reserva 1 contenido mediante clave e Idempotency-Key UUID; 201 primera vez, 200 replay sin nuevo secreto; lease de 10 min. Sin Meta ni publicación. |
| POST `/{target}/begin` | attempt_id + lease + content_hash + asset_reference/hash si hay placa; transición exclusiva previa al POST; 200 primer begin, 409 ya iniciado/lease obsoleto. |
| POST `/{target}/result` | Estado confirmado y metadatos saneados. 200 idempotente. |
| GET `/{target}` | Estado propio, attempt público y resultado; jamás lease, datos de cuentas o payload crudo. |

Los endpoints deben exigir PAT real Sanctum (no cookie de sesión), ability exacta `social-publications:read` o `social-publications:execute`, permiso Spatie correspondiente y usuario no administrador configurado como operador para ese destino. No aceptar el token general de Ingesta con `*` como credencial del nuevo ejecutor; mantenerlo para su uso previo. Provisionar usuario/permiso/token dedicado es un paso humano independiente, nunca un seeder que eleve usuarios existentes. Una herramienta Artisan revisable puede emitir el token al operador una vez, sin pasarlo por argumentos, logs o exportación de workflows.

La configuración `config/social_publication.php` mapea provider/destino a servicio/usuario permitido, tipos implementados y catálogo aprobado. El token no tiene capacidad de cambiar la configuración. No se afirma que ese PAT quede aislado de todas las lecturas legacy de `/api/v1`: sus rutas actuales permiten lectura a tokens válidos; no se amplían permisos ni se refactorizan globalmente en esta entrega. Sí queda sin rol admin y no puede escribir contenido por las rutas admin actuales.

GET usa un Resource dedicado con allowlist: tipo/id/título/bajada opcional/URL canónica/fecha/estado/template y hash. No serializar modelos/relaciones completos ni letras, emails, teléfonos, cuentas sociales, tokens, cuerpo del artículo o usuarios. `per_page` capado, consultas por índices y eager loading solo necesario.

Respuestas 401 sin PAT, 403 destino/permiso, 404 target no visible, 409 conflicto de estado, 422 payload inválido/proveedor deshabilitado, 429 rate limit. Rate limit inicial 60 lecturas/minuto y 30 mutaciones/minuto por operador; validar que no bloquee la cadencia prevista. Nuevo servicio apagado por defecto: candidates, claim y begin devuelven 503 `AUTOMATION_DISABLED`; historial administrativo y GET propios siguen visibles, y result de operaciones ya iniciadas sigue aceptándose con autorización para no perder evidencia durante una pausa.

### 7. Un solo ejecutor y protección del camino legacy

Crear un servicio central de identidad y guard, usado por `PublicationService`, `dispatchJobs`, `PublishToProviderJob`, retry controller y nuevas APIs antes de cualquier envío institucional del destino gestionado. No bastan estados en n8n.

Para targets `executor=n8n`, los jobs Laravel deben salir sin llamar conectores ni alterar estado. Para cualquier target legacy dirigido al destino del piloto, resolver clave y encontrar/reutilizar el ledger; bloquear/derivar al panel sin otro envío automático. No usar `social_account_id` nullable como única identidad. Una creación legacy posterior a un éxito debe devolver el historial/conflicto y no otro target publicable. Un job ya encolado antes del despliegue también debe respetar el guard. Corregir reconocimiento de `success`/`published` sin convertir otro envío en éxito falso. Otros proveedores/cuentas propias conservan su flujo; los tests vecinos deben verificarlo.

La comparación de destino se hace por provider + account_external_id canónico, aun si la misma Página fue registrada como cuenta propia en otro SocialAccount. Duplicar la cuenta lógica en social_accounts o cambiar su propietario no puede eludir el guard del destino gestionado.

La bandera de automatización controla el permiso de nuevos envíos n8n; apagarla no devuelve el destino gestionado a los publicadores legacy. El registro de destinos gestionados sigue obligando a usar el guard, incluso durante pausa. Historial legacy de éxitos: preflight por request contenido + proveedor + cuenta externa. Si hay un intento `success` con ID inequívoco, backfill revisable al target canónico; si hay varios éxitos/targets incompatibles o destino desconocido, bloquear esa clave para revisión. Nunca elegir arbitrariamente uno o borrar/combinar intentos. Jobs en vuelo deben detenerse operativamente en el destino antes de activar el piloto.

### 8. Backoffice y contrato n8n

Página `/admin/social-publications` (rol administrador, policy por acción) con filtros tipo/destino/estado y 15 por página. Mostrar título, canal/cuenta, habilitación, nunca publicado, reservado/enviando/incierto/publicado/error, fecha, ID/URL externa y plantilla. Acciones: habilitar/deshabilitar, reintentar fallo seguro, resolver incierto como publicado con evidencia o como no publicado con motivo y confirmación. No resetear éxitos ni ofrecer republicación en MVP. Fecha del servidor UTC y presentación America/Argentina/Buenos_Aires.

No tocar los forms existentes de Noticias/Eventos para la primera fase. Si se añade create/edit a la nueva pantalla, aplicar todas las reglas AGENTS de form-header/form-actions/wrapper col-12/compact/help/obligatorios y su test Feature. No usar selects múltiples nativos; Select2 para listas extensas si aplica.

Workflow n8n revisable/exportable sin secretos, desactivado: disparador manual de prueba → candidates → claim → producir copy/placa deterministas → begin → UN POST Meta → result. Sin `Retry On Fail` ni bucles automáticos en el nodo que publica. Reintentar callbacks seguros y lecturas con límites. Correlación de ejecución, attempt y hash; sanitizar errores/URL de paginación que pueda traer secretos. Facebook únicamente; marcar por separado `render_ready`, `api_ready`, `publish_tested` y `scheduled_active` en la guía, no inferir uno de otro.

Como render Docker/asset hosting no están listos, entregar y probar todo el protocolo con Meta simulado. No insertar una foto falsa/URL temporal en un flujo activo. El envío real de prueba requiere asset válido, credencial revisada, contenido explícitamente habilitado y autorización de esa publicación concreta. La activación diaria y su horario son decisión posterior. No elegir hosting pago ni abrir servicios locales a Internet.

## Riesgos / Trade-offs

- [HTTP externo sin transacción compartida] → `uncertain` y conciliación humana; no promesa de exactly-once remoto.
- [Dos publicadores] → guard compartido + executor + auditoría de jobs legacy antes de activar.
- [Datos/índices MariaDB] → inventario readonly, migración aditiva por etapas, ensayo sobre entorno ya autorizado, DDL nunca protegido solo por DatabaseTransactions.
- [Historial previo desconocido] → reporte de duplicados y claves ambiguas; bloqueo y decisión humana, no borrado masivo.
- [Contenido portal_only] → aprobación adicional explícita por destino sin cambiar el campo original.
- [Imágenes/derechos] → templates aprobados, solo metadatos y derechos verificados; las otras diez secciones no se activan por tener diseño.
- [Permisos insuficientes o demasiados] → PAT específico no admin y abilities exactas; no entregar tokens de Meta al portal ni reutilizar payloads crudos.
- [Caída del PC] → lease y estados conservadores; disponibilidad diaria sigue dependiendo de n8n/Docker/PC, no se garantiza por el ledger.
- [Caché/SEO/performance] → no cambios públicos; historial autenticado sin caché compartida, índices/eager loading, lote pequeño y sin escaneo por ítem de 12 tablas.

## Migration Plan

1. En la rama activa, preservar cambios. Leer esta propuesta, specs y `project/docs/00_estado_actual.md`; inspeccionar migrations reales y motor/charset/índices mediante solo lectura. No dar por ejecutado el preflight de esta spec.
2. Preparar migraciones separadas: crear habilitaciones; agregar columnas nullable/índices a targets/attempts; constraints tras reporte de duplicados. Ninguna borra tablas, renombra columnas ni habilita contenidos.
3. Preparar comando readonly `social-publications:audit-legacy` con IDs/claves/conteos y errores de compatibilidad. No imprimir secretos/payloads privados. Backfill es otro comando revisable sin ejecución por defecto, lotes idempotentes y transacción por clave.
4. Presentar migración y plan. Antes de aplicar, mostrar `php artisan migrate:status` del entorno concreto y esperar confirmación explícita. Esa confirmación no autoriza backfill DML ni producción: pedir/aplicar cada alcance autorizado. No usar fresh/refresh/truncate ni crear bases de prueba.
5. Verificar DB efectiva de tests y que exista una base de testing ya autorizada. Si no, terminar preparación y reportar bloqueo, sin ejecutar tests de BD sobre mfa. Revisar fixtures/seeders y reemplazar RefreshDatabase en los tests que se ejecutarán antes de correrlos.
6. Correr Feature aislados con DatabaseTransactions, Queue::fake y Http::fake; pruebas de carrera con dos conexiones solo en entorno autorizado y limpieza transaccional controlada. Si no se puede demostrar carrera con DB real, checklist explícito pendiente; un test secuencial no acredita concurrencia.
7. Integración n8n apagada contra Meta simulado; revisar panel, seguridad, callback duplicado, lease expirado y error incierto. Documentar rendimiento básico y consultas, sin inferir resultados productivos.
8. Deploy, emisión de credencial, backfill real, habilitación de destino, publicación de prueba y programación diaria son pasos humanos independientes y posteriores, con evidencias y autorizaciones.

Rollback seguro: apagar la automatización y retirar la credencial específica; preservar ledger/historial. No ejecutar automáticamente down tras uso real, pues eliminaría evidencia y permitiría duplicados. Down estructural solo para un ensayo vacío autorizado; ante datos reales se prepara reversión revisable y se conserva evidencia.

## Open Questions / Activation Gates

No bloquean escribir el código: se eligieron defaults conservadores (Facebook institucional, News/Eventos, sin republicación, habilitación por registro, automático apagado). Deben resolverse antes de activar: estado real de Pasarela e índices; mapeo exacto del destino/cuenta; operador/PAT dedicado; renderizador Docker y alojamiento de placas; publicación concreta de prueba; horario diario/canal de alertas; ventana editorial de antigüedad de noticias. No habilitar todo el archivo mientras no se defina esa ventana; candidatos requieren habilitación individual. Si el preflight encuentra conflicto de esquema/historial que invalida la decisión de reutilizar Pasarela, documentar alternativa y volver a revisión, no crear un ledger paralelo en silencio.

## Continuación autorizada 2026-11-10: PNG binario y dos piezas

El usuario pidió continuar para dejar la automatización lista hoy, Noticias primero y Eventos como segunda pieza. Se extiende el alcance local aprobado: renderer GD determinista con la identidad v4 y fuente Segoe UI configurada externamente (no redistribuida); endpoint POST /{target}/render autenticado con lease/attempt antes de begin, salida PNG base64 + SHA256 y referencia sha256:<digest>. El archivo se guarda en storage privado por digest y begin verifica su existencia/hash para referencias binarias. Permanece compatible el modo HTTPS previo. n8n carga source multipart directamente a Meta; no se requiere hosting público ni puertos nuevos. Snapshot incorpora metadatos verificables de fecha/lugar para eventos. Sin IA ni dependencias nuevas.

Se preparan dos workflows separados, Noticias y Eventos, con manual + horario argentino propuesto (10/18, pendiente respuesta del usuario), configuración no secreta en nodo Code sin variables comerciales de n8n. Estado inactivo por defecto y simulación sin Meta. Importar borradores en n8n local está dentro del pedido; publicar/activar o desplegar en producción requiere alcance concreto autorizado. Preparar y revisar todo antes de solicitar ese último paso. El portal productivo aún no tiene acreditado el ledger nuevo y no se sustituye por Excel ni ledger paralelo silenciosamente.

Validación: tests Feature de render/huella/aislamiento, PNG 1080x1350 y legibilidad de ambas piezas con contenido real solo de lectura. No asumir que producción comparte los datos de la BD local. Test de flujo local sin publicar ni habilitar contenidos persistentes, con fixtures en transacciones autorizadas de mfa_test. No emitir PAT ni escribir habilitaciones/usuarios reales sin la autorización correspondiente.

## Autorización recibida: operador y entrega del release (2026-11-10)

El usuario autorizó operador dedicado no administrador y PAT limitado guardado en su n8n; eligió realizar él mismo el deploy en Hostinger. Preparar provisión CLI idempotente, dry-run por defecto, sin emitir PAT ni tocar datos productivos desde esta sesión. La credencial debe emitirse en la misma BD del portal real tras el despliegue/migración que ejecuta el usuario. Se agrega salida opcional a archivo privado exclusivo para import n8n, sin mostrar el secreto, sin sobrescribir archivos existentes y con permisos restrictivos; tokens no se copian a código/docs/chat. No se creará un operador local inútil ni se presentará una credencial local como productiva.
