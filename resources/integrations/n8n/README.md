# MFA: publicación controlada con ledger

## Estado

Implementación local del portal. Facebook institucional y tipos `news` / `events` exclusivamente. Los exports de `daily/` permanecen inactivos/bloqueados por seguridad al importar. La instancia de Noticias fue activada por el usuario a las11:00 Argentina el2026-11-11, tras una publicación real833 verificada; Eventos sigue manual. Primero se probaron seis escenarios con simulador privado. Estado y pasos vigentes en `ACTIVATION.md`. El export original `mfa-social-publications.inactive.json` se conserva como referencia inicial; usar los nuevos borradores. Instagram Noticias quedó implementado localmente, inactivo y pendiente de deploy/prueba real (guía `INSTAGRAM.md`); otras redes quedan para otra etapa.

Las 12 piezas v4 tienen aprobación visual del usuario; esa aprobación no habilita contenidos ni autoriza publicaciones. El catálogo conserva su estado histórico de borrador; el registro operativo de templates permitidos para el piloto está en `config/social_publication.php` (Noticias y Agenda).

## Preparación antes del flujo real

1. Revisar acceso al portal local: `/admin/social-publications`, rol `administrador`.
2. Crear/configurar un operador NO administrador con permisos explícitos `social-publications:read` y `social-publications:execute`. No existen seeders ni concesiones automáticas. Configurar su ID en `SOCIAL_PUBLICATION_OPERATOR_IDS`.
3. Tras autorización para manejar la credencial, ejecutar `php artisan social-publications:issue-token ID --issue` en un canal privado. Guardar el PAT solo como credencial HTTP Header Auth de n8n, encabezado Authorization: Bearer …; no usar el token wildcard de Ingesta. El comando sin `--issue` solo verifica.
4. Los borradores de `daily/` configuran API, tipo y bloqueo en el nodo Code «Configuración y bloqueo». No requieren variables de pago. Mantener allow_publish=false hasta completar despliegue, credenciales y prueba autorizada.
5. El renderer GD v4 está implementado en el portal: POST /{target}/render devuelve asset_base64, SHA256 y referencia sha256:<digest>. n8n convierte a binario y envía source multipart a Meta. No requiere alojamiento público ni abrir puertos. Configurar las fuentes editoriales privadas antes de desplegar; el renderer Windows histórico no es el utilizado por estos borradores.
6. Vincular credencial PAT en nodos de API y credencial Meta separada en el nodo Meta. Ninguna credencial está embebida o vinculada por ID en el export.
7. Verificar `APP_URL` público correcto, fuentes editoriales, derechos de imágenes, token Meta, inexistencia de jobs legacy en vuelo y un contenido público vigente habilitado explícitamente. Noticias automática incorpora portal_only bajo la política global autorizada; Eventos sigue requiriendo override individual.
8. Activar la bandera local solo tras validar todo lo anterior. La migración no cambia `.env`, permisos, credenciales, contenidos ni tareas programadas.
9. Obtener autorización para una publicación concreta antes de ejecutar el flujo contra Meta. Luego evaluar horario y activación diaria por separado.

## Protocolo

`GET candidates` → `POST claims` con Idempotency-Key UUID → render validado → `POST /ID/begin` → **un** POST Meta → `POST /ID/result`.

El endpoint `begin` exitoso permite un único envío. Un replay devuelve 409; si se pierde su respuesta, no continuar ni reenviar: conciliar. El UUID evita recrear intentos; repetir claim devuelve el mismo ID SIN lease. Si se pierde el lease, esperar vencimiento antes de reservar de nuevo. La reserva dura 10 minutos; máximo tres envíos aceptados por begin, backoff 60 s y 5 min antes de los siguientes; el tercer fallo queda bloqueado (retry_after conserva 30 min como evidencia).

Cada borrador procesa como máximo un candidato por ejecución. Noticias incluye scheduler preparado; primero se prueba manualmente con autorización. Eventos no tiene scheduler. No reintentar la ejecución completa después de Meta. Solo el callback es reintentable. Meta 5xx, timeout, respuesta incompleta o pérdida de respuesta se reportan `uncertain`. Un rechazo inequívoco exige HTTP 4xx distinto de 408, error de proveedor y `failure_evidence=provider_rejected`. No convertir errores de transporte en rechazo seguro.

Para fotos, usar `post_id` de Meta, no el ID de la foto; si falta el post_id se concilia como incierto. El ID confirmado debe ser `<PAGE_ID>_<POST_ID>`. No se acepta URL remota arbitraria: el portal deriva el enlace Facebook del ID validado.

## Recuperación

`php artisan social-publications:reconcile` revisa reservas: reserved vencido pasa a pending/cancelled; processing vencido pasa a uncertain. No consulta Meta ni publica, y no se agregó al scheduler automáticamente. Debe programarse junto con el flujo cuando se autorice su activación.

Los éxitos son monotónicos. Editar una noticia, retirar su habilitación o borrar el autor no abre otra publicación. El callback tardío de éxito puede resolver un uncertain del mismo intento, pero una conciliación manual invalida su lease. La marca de publicación usa la fecha de confirmación del servidor, no una fecha remota inferida. Base en UTC, panel en hora argentina.

Para resolver uncertain, el administrador verifica en Facebook y registra motivo/evidencia e ID cuando corresponde. «No publicado» exige confirmación explícita, espera 30 min y respeta el límite de tres envíos. No existe botón de reset de éxitos. La API de operador no puede habilitar contenidos ni conciliar manualmente.

Pausa: `SOCIAL_PUBLICATION_ENABLED=false` bloquea candidatos, claims y begin. Mantiene consulta y callbacks de envíos iniciados. El guard de Pasarela sigue activo aun en pausa: no hay fallback a un segundo publicador.

## Historial y migración

Migración `2026_11_10_100000_add_social_publication_ledger.php`: habilitaciones por destino, columnas aditivas de targets/attempts, unicidad local y remota, secuencia de intentos y autor nullable con SET NULL. Índices compatibles con MariaDB 10.8/utf8mb4. La migración permite retomar sus fases de creación tras fallo parcial; no autoriza reparar estructuras incompatibles. No tiene down destructivo: una reversión exige migración específica revisada.

`social-publications:audit-legacy` es solo lectura. `social-publications:backfill` es dry-run por defecto y solo propone éxitos de un único intento y target inequívocos para la Página configurada. `--apply` modifica datos y requiere autorización propia. Claves ambiguas, cuenta ausente, estado no exitoso o ID incompleto no se inventan ni se fusionan. En esta sesión el historial local consultado estaba vacío y no se ejecutó backfill con escrituras.

## Validación y pendientes operativos

Pruebas Feature en `tests/Feature/SocialPublicationLedgerTest.php`, transacciones en la base preexistente `mfa_test`, Http::fake y Queue::fake. Las pruebas Feature no publican externamente; además se ejecutó el workflow en n8n contra un mock privado, con seis escenarios exitosos. Ver evidencia final en `project/docs/social-publication-implementation.md`.

Checklist de aceptación al conectar el flujo: dos ejecuciones simultáneas y solo un claim; pérdida de respuesta de claim y begin; caída después de Meta; pérdida de callback y reenvío solo de callback; timeout queda uncertain; rechazo inequívoco respeta backoff. La carrera física con dos conexiones independientes queda pendiente de validación operativa; render local e import de borradores sí se validaron. No se promete exactly-once remoto.

No habilitar guardado de datos de ejecuciones de n8n: claims contienen lease en claro. El export desactiva retención de resultados, pero el editor puede mostrar datos de ejecuciones manuales mientras se trabaja. Restringir acceso al editor y evitar capturas con secretos. Logs nuevos del portal no guardan tokens; el saneamiento no elimina histórico preexistente.

## Alcance y costo

Sin IA, tokens de IA, Canva pago ni dependencias nuevas. Render determinista sobre las plantillas aprobadas. Se agregan consultas autenticadas paginadas y pequeñas transacciones; candidatos carga contenidos por lote, no por fila. No se alteran rutas públicas, slugs, canonical ni SEO. El estado local no acredita despliegue ni resultados de tráfico.

## Selección sin intervención por noticia (actualización pendiente de deploy)

El usuario pidió selección automática: después de desplegar la actualización, candidates toma News activa/visible, ID>833 y orden ascendente sin consultar un opt-in manual. Claim registra el seguimiento automáticamente. Una por ejecución; éxitos y estados inseguros no se repiten. El workflow existente de las11no requiere reimportación ni cambios de credencial. Desplegar PHP/config/vista y regenerar config:cache/view:clear; sin migraciones. Eventos manual. Hasta ese deploy, la API productiva aún conserva su comportamiento anterior.
