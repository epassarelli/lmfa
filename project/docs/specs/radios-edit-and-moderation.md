# Cambio: radios-edit-and-moderation

## Objetivo y aprobación
Pedido explícito del usuario del 2026-11-11: rama nueva desde dev, corregir edición HTTP 500 y llevar radios draft al Centro de Moderación para revisión/publicación. Rama `codex/radios-edicion-moderacion`. Alcance autorizado local; sin deploy, SQL, migraciones ni publicaciones reales.

## Problema actual
El usuario informa HTTP 500 al editar radios importadas con canales. Los tests anteriores de edición usan fixtures sin canales. El listado ABM incluye borradores y ModerationQueue no contempla RadioSignal, aunque el modelo canónico sí tiene editorial_status. La nota anterior que afirmaba que radios no tiene estado corresponde al legado y no al dominio canónico.

## Alcance incluido / archivos afectados
- RadioSignalController, formularios de señales y channel-row: reproducción y corrección del fallo con datos importados, preservando campos/canales inactivos y errores de validación.
- ModerationQueue, ModerationController, vista de moderación: tarjeta Radios contraída, revisión, publicación mediante RadioDirectoryService y contadores compartidos.
- Índice ABM de señales: excluir draft incluso si se solicita filtro draft; enlace al Centro para administradores. Mantener edición directa de borradores propios para colaboradores.
- Redirección tras guardar un borrador revisado por administrador al Centro; contenido publicado al ABM. Publicar requiere permisos y todos los requisitos existentes de verificación/escucha; no verificaciones automáticas.
- Tests Feature y documentación de estado.

## Fuera de alcance
Programas de radio, peñas, APIs/URLs públicas, datos productivos, autorización automática de las 50 fichas, nuevas dependencias y cambios de esquema.

## Reglas funcionales y aceptación
1. Dada una señal importada con fuentes y canales inactivos, cuando un administrador abre edición, entonces recibe 200 y conserva sus valores; guardar errores muestra validación y no 500.
2. Dadas señales draft y publicadas, cuando se consulta el ABM, entonces sólo las no draft aparecen; los draft se muestran en el Centro y en su contador, con autor, provincia y fecha, y enlaces de revisión.
3. Dada una señal draft incompleta, cuando se intenta publicar desde moderación, entonces se muestran requisitos faltantes y persiste draft. No se activan canales ni se simula verificación.
4. Dada una señal completa, verificada y con escucha activa, cuando administrador publica, entonces pasa a published, sale de moderación y aparece en ABM; la tarjeta queda abierta si hay más pendientes.
5. Colaboradores no acceden al Centro ni publican; conservan edición directa de borradores propios. Guardar un borrador por administrador vuelve a moderación.

## Reglas técnicas, performance y SEO
Conservar policy y servicio transaccional canónico; invalidar contador tras escrituras de señales, incluyendo API. Eager loading de autor/provincia, sin consultas por fila. Sin cambios de rutas públicas, slugs existentes, visibilidad, sitemap ni SEO. Formularios tocados conservan layout compacto, ancho completo y acciones estándar.

## Validación prevista
Reproducir error con Feature de edición incluyendo canal y old input. DatabaseTransactions en base existente mfa_test, sin migrations/seeders ni RefreshDatabase; comprobar conexión antes de ejecutar. Probar separación ABM/moderación, contador, permisos, publicación bloqueada y válida, redirecciones y pruebas vecinas de radios/moderación.

## Resultado validado (local)
Estado: `done` en implementación local. Cierre y commit en la rama de radios autorizados por el usuario el 2026-11-11. No implica integración en dev ni despliegue.
- Rama creada desde dev preservando cambios locales ajenos.
- Reproducción del caso aclarado por el usuario: abrir edición funciona; intentar publicar una ficha incompleta provoca validación y, al volver, `implode()` recibe `source_urls` como string del old input. Test previo falló con HTTP 500 y ese TypeError. Se corrige la representación de fuentes para aceptar string y array, preservando los valores editados y el rechazo transaccional de una publicación incompleta.
- Draft fuera del ABM; tarjeta Radios en Borrador en Centro con revisión, publicación canónica, autor/provincia/fecha, mensajes de error y reapertura. Contadores compartidos incluyen radios y se invalidan tras altas/ediciones/publicación/despublicación de señales a través del servicio (incluye API).
- Guardar draft como administrador retorna al Centro; colaborador retorna a edición propia. Formularios conservan acciones compactas, wrapper col-12 y encabezado de edición con nombre del registro.
- 42 tests / 297 assertions aprobados: `Radio|ModerationCenterTest|AdminAttentionDashboardTest`. DatabaseTransactions, APP_ENV=testing y DB_DATABASE=mfa_test. Se usó `VIEW_COMPILED_PATH=/tmp/mfa-radio-test-views` y `--do-not-cache-result`: el caché local habitual tiene archivos sin permiso de escritura para www-data. No se cambiaron esos permisos ni datos de mfa.
- Sin migraciones, necesidad de build frontend, SQL manual, publicación productiva, push ni deploy. Cambios preparados para commit de cierre en la rama de radios. Eager loading de autor/provincia en tarjeta; un count adicional para radios en el cómputo cacheado de moderación. Sin cambios de rutas/SEO público. La prueba de navegador productiva fue únicamente GET de edición; no se intentó publicar ni guardar contenido real.
- Pendiente: despliegue autorizado por separado y comprobación del circuito con el servidor actualizado. Las 50 radios productivas permanecen sin modificación.
