# Configuración y variables de entorno del portal

Referencia de las variables que **cambian el comportamiento** del portal (no incluye credenciales de base, claves ni servicios de terceros). Actualizada el 2026-10-13. Fuente de verdad: `config/*.php` y `.env.example`.

## Principio

Las variables de comportamiento fueron **andamios de despliegue por etapas**, no opciones permanentes. Cuando se decide el camino, la variable se elimina y el código queda con una sola ruta. Hoy queda un solo interruptor con impacto real.

## Variables

| Variable | Por defecto | Qué hace | Cuándo tocarla |
|---|---|---|---|
| `BACKOFFICE_ACCESS_BY_ENTITIES` | `false` | **Acceso por fichas.** Apagada: cada rol ve las secciones que sus permisos permiten. Prendida: quien no es administrador solo ve y edita lo que corresponde a las fichas (artistas/festivales) que administra; el alta directa de artistas y festivales queda para administradores y `verificado` (el resto usa «Solicitar alta»); el listado de Artistas se acota a lo propio. Los verificados entran además a Noticias, Mitos, Comidas, Artistas y Festivales por su rol. | Etapa 2 del despliegue, después de avisar a los colaboradores. Detalle en `permisos_y_roles.md`. |
| `ENTITY_CLAIMS_DELEGATE_TO_OWNERS` | `true` | Los Responsables (`owner`) de una ficha aprueban o rechazan a quienes piden sumarse como Editor o Miembro. En `false`, solo aprueba un administrador. | Casi nunca. Candidata a eliminarse. |
| `SOCIAL_LOGIN_ENABLED` | `false` | Muestra los botones de Google y Facebook en ingreso y registro. | Solo si el login social está configurado y probado. Candidata a derivarse de la presencia de credenciales. |
| `CONTACT_NOTIFY_TO` | `mifolkloreargentino@gmail.com` | Casilla que recibe el aviso de cada mensaje del formulario de contacto. No es un interruptor. | Si cambia la casilla del equipo. |
| `CONTACT_SEND_CONFIRMATION` | `true` | Envía un correo de confirmación a quien completa el formulario de contacto. | Casi nunca. Candidata a eliminarse. |
| `NEWSLETTER_MAX_PER_MINUTE` | `60` | Tope de correos del newsletter por minuto (los excedentes vuelven a la cola). Valor de ajuste, no interruptor. | Según el límite del proveedor de correo. |
| `QUEUE_CONNECTION` | `sync` en `.env.example` | **En producción debe ser `database`** (el newsletter y las publicaciones usan la cola). | Una vez, al configurar producción. |

## Eliminadas

- `ENTITY_MANAGEMENT_SCOPE_LISTINGS` (2026-10-13): limitaba solo el listado de Artistas y era redundante con `BACKOFFICE_ACCESS_BY_ENTITIES`. Si figura en un `.env`, se ignora y puede borrarse.

## Cosas que **no** son variables

- **Registro sin rol:** quien se registra no recibe ningún rol (comportamiento fijo, ver `permisos_y_roles.md`).
- **Envío del newsletter:** nada se envía sin aprobar una edición (comportamiento fijo, ver `releases/newsletter-produccion.md`).
- **Ajuste de imágenes:** los tamaños sugeridos y los avisos están en `config/backoffice_help.php`; el ancho máximo sale de `config/image_profiles.php`.
- **Textos de ayuda del backoffice:** `config/backoffice_help.php`.

## Candidatas a simplificar (decisión pendiente)

Una vez elegido definitivamente el modelo de acceso por fichas, se pueden retirar `BACKOFFICE_ACCESS_BY_ENTITIES`, `ENTITY_CLAIMS_DELEGATE_TO_OWNERS`, `CONTACT_SEND_CONFIRMATION` y `SOCIAL_LOGIN_ENABLED`, dejando un único camino. Requiere que los colaboradores existentes tengan sus fichas vinculadas antes de salir, porque el rollback pasaría a ser volver al despliegue anterior.
