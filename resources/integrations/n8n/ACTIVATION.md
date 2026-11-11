# Activación Noticias — revisión 2026-11-10

## Estado concreto

- Usuario eligió **solo Noticias por ahora**. Workflow diario: `MfaNewsDailyV401`, **11:00 Argentina**, activado y verificado en la instancia n8n con autorización expresa del usuario el2026-11-11. Los exports fuente permanecen inactivos/bloqueados por defecto para evitar activaciones accidentales al importar.
- Autorizado por el usuario: operador dedicado y credencial en n8n. El usuario realizará el deploy; el operador/PAT productivos se crean después, en esa misma base. No se emitió un token local como sustituto.
- Eventos: `MfaEventsDaily01`, solo manual, sin trigger horario. No activar diariamente.
- Meta: con autorización específica del usuario el 2026-11-11 se obtuvo y guardó el token de Página en la credencial existente; GET /me devuelve Mi Folklore Argentino, ID 145931542128207. No se rehízo la app, cambiaron permisos ni publicaron contenidos.
- Producción: rutas desplegadas y protegidas; el usuario aportó preflight de PHP 8.2.35, GD, fuentes, esquema y HTTPS correctos, operador 453 configurado. Credencial dedicada importada en n8n y probada: lectura 503 por pausa, estado inexistente 404, ejecución 503 por pausa. Esto confirma autenticación y ambos permisos; sin reservas ni publicaciones.
- PNG reales generados sin editar los contenidos; posteriormente el usuario autorizó expresamente la prueba de noticia833, publicada y verificada con imagen/copy correctos. Evento392 quedó únicamente como preview, sin publicar.
- Seis escenarios del workflow pasaron contra simulador privado antes de probar Meta. Luego UNA publicación833 autorizada se ejecutó en Facebook y se verificó; ver el resultado y la activación del2026-11-11 al final de esta guía.

## Paso de producción (sin ejecución automática)

El flujo vigente del proyecto es revisión/CI/merge humano y deploy en Hostinger. Commit local autorizado y creado; el usuario informó que hizo push y ejecutó migraciones productivas sin error. Continúa la validación autenticada antes de activar.

1. Revisar los archivos del release local. El paquete no contiene `.env`, tokens, fuentes Microsoft, datos de BD ni cambios ajenos al subproyecto.
2. Desplegar código por el flujo habitual. No cambiar `SOCIAL_PUBLICATION_ENABLED=false` aún.
3. Configurar fuentes Segoe UI regular/bold provistas por el titular en rutas privadas legibles por PHP; `SOCIAL_PUBLICATION_FONT_REGULAR` y `SOCIAL_PUBLICATION_FONT_BOLD`. Las copias de Docker `/tmp/mfa-fonts` son solo runtime local; no forman parte del release ni garantizan persistencia al recrear el contenedor. Si el hosting no dispone de esa fuente, validar una alternativa visual antes de activar.
4. Ejecutar `php scripts/social-publication-preflight.php` y **mostrar `php artisan migrate:status` antes de autorizar la migración productiva**. La autorización de migraciones locales no habilita producción. Confirmar respaldo vigente mediante el procedimiento del hosting.
5. Con confirmación del responsable para ese entorno, aplicar exclusivamente `database/migrations/2026_11_10_100000_add_social_publication_ledger.php`. No usar fresh/refresh/down ni ejecutar todas las pendientes sin revisión. Si hay historial legacy, correr audit y dry-run; no aplicar backfill sin autorización de datos propia.
6. Crear un operador técnico NO administrador, sin contraseña interactiva conocida, y permisos explícitos `social-publications:read` / `social-publications:execute`. Configurar ID permitido. No asignar rol administrador ni usar el token de Ingesta. Este paso altera usuarios/permisos y requiere autorización humana.
7. El comando `social-publications:issue-token ID` comprueba; `--issue` emite una sola vez tras autorización. Guardar el resultado únicamente en HTTP Header Auth de n8n, Authorization: Bearer …, en privado. No pegarlo en el chat ni el repositorio. Enlazar la credencial a todos los nodos de API del borrador diario.
8. Configurar `APP_URL=https://mifolkloreargentino.com`. Comprobar fonts/GD, API privada, permisos y que el token Meta responde como Página. Limpiar/cargar caché con el flujo de despliegue existente. No modificar parámetros de OAuth ni regenerar permisos por ensayo.
9. Detener/conciliar jobs legacy institucionales en vuelo, revisar auditoría, habilitar individualmente noticia pública y vigente en el panel, incluyendo override portal_only si corresponde. No habilitar masivamente el archivo.
10. Activar bandera del servicio y autorizar una prueba concreta de noticia/PNG/copy en la Página MFA. Cambiar allow_publish solo para esa ejecución manual; verificar ID/visibilidad/registro, sin reenviar a Meta si falta la confirmación.
11. Con esa prueba correcta y autorización final, publicar el workflow de Noticias en n8n y programar el reconciliador de reservas por el mecanismo operacional ya previsto. Mantener Eventos manual. Si no hay candidato habilitado, la ejecución omite publicar; no inventa contenido.

La incorporación automática de nuevas noticias a habilitaciones todavía exige una decisión editorial: ventana de antigüedad y permiso por defecto frente a portal_only. El piloto mantiene habilitación individual. Sin esa decisión, el proceso diario consume solo la cola aprobada; no selecciona todo el archivo ni cambia publicación_mode.

## Comandos concretos después de subir el release

Primero revisar `php artisan migrate:status` y aplicar la migración indicada en el paso 5 solo con confirmación para producción. Después:

```bash
php artisan social-publications:provision-operator
php artisan social-publications:provision-operator --apply
```

El primer comando es dry-run. El segundo, ya autorizado por el usuario, crea o reutiliza únicamente la identidad técnica reservada, sin roles ni contraseña conocida, concede read/execute y muestra `SOCIAL_PUBLICATION_OPERATOR_IDS=ID`. Si encuentra una identidad usada para otra finalidad, se detiene. Agregar esa línea al `.env` del servidor, conservar enabled=false y actualizar la configuración con el procedimiento de cachés del hosting. Entonces:

```bash
php artisan social-publications:issue-token ID --issue --n8n-file
```

Reemplazar ID por el mostrado por el comando anterior. El secreto no se imprime: queda en `storage/app/private/social-publications-n8n.json`, creado de manera exclusiva con permisos privados. No ejecutar sin `--n8n-file` si se quiere evitar mostrarlo en terminal. Si ya existe el archivo, no se sobrescribe ni se emite otro token.

Transferir ese único archivo por el canal privado habitual del hosting a la PC, sin subirlo a public_html ni pegarlo en el chat. Luego importar en el n8n local:

```powershell
docker cp "RUTA_PRIVADA_DEL_ARCHIVO" n8n:/tmp/mfa-social-credential.json
docker exec n8n n8n import:credentials --input=/tmp/mfa-social-credential.json --projectId=cKXWgm1XbGu1Xoy9
```

Los borradores ya referencian el ID previsto `MfaSocialPortal1`, nombre `MFA - Publicaciones Portal`. La importación conserva el token cifrado por n8n; el archivo de transferencia sigue siendo sensible. Retirarlo de los archivos temporales por el procedimiento privado acordado después de verificar la importación. No incluirlo en backups compartidos o capturas. Importación real completada y verificada el 2026-11-11, sin mostrar el secreto. Se retiraron las copias temporales creadas por el agente dentro de n8n; el original del usuario permanece en storage/app/private, ignorado por Git.

## Manejo de errores y continuidad

No reejecutar un workflow completo que alcanzó Meta. Revisar estado y recuperar solo callback/conciliación. Desactivar bandera y workflow detiene nuevos envíos sin borrar historial. La PC y Docker deben seguir funcionando; no se contrató un servicio externo.

Se ejecutaron 34 tests del ledger/render local y seis escenarios en n8n simulado; ninguna llamada de publicación a Facebook. Las previsualizaciones y el simulador no validan producción ni credenciales de Página.

## Prueba real preparada el 2026-11-11

Usuario confirmó API habilitada y autorizó noticia833 para prueba con override portal_only. Claim/render de producción completados desde n8n: PNG1080x1350 íntegro y revisado, target1/attempt1, envíos0. Imagen y copy presentados al usuario; UNA publicación real pendiente de aprobación específica. Borrador one-off MfaNewsOneTest01 inactivo y bloqueado, solo para esa noticia y hashes aprobados. Noticias diaria sigue inactiva; Eventos manual. Sesión web admin expiró durante intento de liberar la reserva; no se modificó éxito ni se envió nada. Recuperación por vencimiento pre-begin y claim transaccional, sin reenviar Meta.

## Resultado de UNA prueba autorizada — 2026-11-11

Publicación833 completada y verificada: Meta200, post145931542128207_1618780120285817, imagen/copy coinciden y portal success/attempts_count1. Enlace canónico: https://www.facebook.com/1618781103619052/posts/1618780120285817. GET candidates ya excluye833. One-off nuevamente bloqueado/inactivo, diaria todavía apagada y pendiente de aprobación. La cola actual está vacía: no habrá nuevas publicaciones sin habilitar otros contenidos. Recuperación periódica de reservas sigue requiriendo programar social-publications:reconcile en el hosting; no está incorporada automáticamente al scheduler de Laravel.

## Estado final de operación — 2026-11-11

Noticias activa a11:00 America/Argentina/Buenos_Aires; active/published confirmados y log de activación efectivo tras recarga. Usuario autorizó ese horario y Noticias activas desdeID834 tras el deploy automático. Prueba833 publicada y registrada una vez; cola actualmente vacía. Eventos manual sin scheduler. Si no hay candidata, no publica. La PC, Docker y n8n deben seguir funcionando. El reconciliador periódico de Hostinger sigue como pendiente operacional; no se instaló cron por iniciativa propia. Los exports de repositorio se guardan inactivos/bloqueados por seguridad, aunque esta instancia esté activa. No hacer import encima de la instancia sin revisar publicación/configuración.

## Regla automática solicitada por el usuario — actualización pendiente de deploy

Noticias ya no requiere habilitar cada contenido: el portal selecciona ID>833 en orden ascendente, con editorial_status published y published_at no futura, exactamente como el frontend. Marca publicación únicamente tras éxito; excluye éxitos/processing/uncertain/lease vigente e historial legacy del mismo destino. Claim crea las marcas faltantes y las audita automáticamente; no se habilita todo el archivo ni se cambia publication_mode. Una noticia omitida por inactiva puede entrar si se activa después. No hay ventana máxima de antigüedad adicional.

Defaults nuevos: SOCIAL_PUBLICATION_NEWS_AUTOMATIC=true, SOCIAL_PUBLICATION_NEWS_AFTER_ID=833. Conservar el ID real del operador, fuentes y bandera enabled actual. Para usarlo hay que desplegar PHP/config/vista de esta actualización y ejecutar php artisan config:cache y php artisan view:clear. No hay migraciones nuevas, no se emiten tokens ni se importa/republica el workflow n8n existente. N8n sigue activo a11 y consulta el mismo endpoint; la selección nueva entra al desplegar el portal. Hasta ese deploy, la instancia productiva conserva la regla anterior.

En modo automático la pantalla muestra que Noticias no requiere el formulario, reservado a Eventos. Para retirar una Noticia de la difusión se desactiva en el portal o se pausa el servicio completo. El opt-in manual histórico puede conservarse con news_automatic=false si el usuario decide volver a ese modo. Los bloqueos de seguridad no son aprobaciones editoriales manuales: protegen contra duplicados.

## Actualización automática desplegada y verificada — 2026-11-11

El usuario confirmó deploy/config:cache/view:clear; n8n consultó producción sin mutar datos: candidatos200 devuelve5 noticias834-838 en orden ascendente, primera834 (David Robles / Somos Tucumán). La833 se excluye y conserva success/1envío. Enlace público de834 verificado200. Workflow diario sigue active=true,11Argentina. Esta evidencia reemplaza las notas anteriores de deploy pendiente: selección sin opt-in manual ya operativa. Sin nuevas publicaciones durante esta comprobación; la próxima ejecución tomará la primera activa aún no enviada. Eventos manual.
