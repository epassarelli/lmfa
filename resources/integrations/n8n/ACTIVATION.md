# Activación Noticias — revisión 2026-11-10

## Estado concreto

- Usuario eligió **solo Noticias por ahora**. Borrador diario: `MfaNewsDailyV401`, 10:00 Argentina propuesto. Está importado, inactivo y con `allow_publish:false`.
- Autorizado por el usuario: operador dedicado y credencial en n8n. El usuario realizará el deploy; el operador/PAT productivos se crean después, en esa misma base. No se emitió un token local como sustituto.
- Eventos: `MfaEventsDaily01`, solo manual, sin trigger horario. No activar diariamente.
- Meta ya está vinculado por referencia a `MFA - Facebook Página`. El diagnóstico GET de esa credencial responde **Edu Passa**, no la Página: debe obtenerse/guardarse un token de Página y verificar GET me = 145931542128207 antes de publicar. No es necesario rehacer la app ni Ingesta.
- API de lectura del portal verificada con contenido publicado real. El endpoint de ledger productivo devuelve 404: falta desplegar el desarrollo.
- PNG reales de noticia 833 y evento 392 generados localmente sin modificar artículos, eventos ni habilitaciones. Se muestran sin marca de simulación porque son salida final del renderer, pero **no están autorizados ni enviados como publicaciones**.
- Flujo real ejecutado en n8n únicamente contra simulador loopback: éxito, rechazo, 5xx, pérdida de respuesta Meta, pérdida de callback y pérdida de begin. Un solo POST al simulador; pérdida de callback reintenta solo callback; pérdida de begin no envía.

## Paso de producción (sin ejecución automática)

El flujo vigente del proyecto es revisión/CI/merge humano y deploy en Hostinger. El usuario autorizó el commit local de esta entrega; la subida y el despliegue siguen pendientes.

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

Los borradores ya referencian el ID previsto `MfaSocialPortal1`, nombre `MFA - Publicaciones Portal`. La importación conserva el token cifrado por n8n; el archivo de transferencia sigue siendo sensible. Retirarlo de los archivos temporales por el procedimiento privado acordado después de verificar la importación. No incluirlo en backups compartidos o capturas. No se ejecutó esta importación de credencial real durante el desarrollo.

## Manejo de errores y continuidad

No reejecutar un workflow completo que alcanzó Meta. Revisar estado y recuperar solo callback/conciliación. Desactivar bandera y workflow detiene nuevos envíos sin borrar historial. La PC y Docker deben seguir funcionando; no se contrató un servicio externo.

Se ejecutaron 34 tests del ledger/render local y seis escenarios en n8n simulado; ninguna llamada de publicación a Facebook. Las previsualizaciones y el simulador no validan producción ni credenciales de Página.
